<?php

namespace App\Services\Ai;

use App\Models\Amenity;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Support\Str;

class AiChatService
{
    public function __construct(
        private OpenAiTextService $openAi,
        private AiPageContextService $pageContext,
    )
    {
    }

    public function answer(string $message, array $history = [], array $page = [], ?User $user = null): array
    {
        $message = trim($message);
        $history = $this->sanitizeHistory($history);
        $pageDetails = $this->pageContext->build($page, $user);
        $intent = $this->detectIntent($message);
        if ($this->shouldUseCurrentPageContext($message, $intent, $pageDetails)) {
            $intent = 'page_context';
        }
        if ($intent === 'off_topic' && !empty($pageDetails['has_record'])) {
            $intent = 'page_context';
        }
        $shouldSearchHotels = $this->shouldSearchHotels($message, $intent);

        if (!$shouldSearchHotels && $intent === 'off_topic' && empty($pageDetails['has_record'])) {
            return [
                'answer' => 'Mình có thể trò chuyện về Travel Mate, du lịch, khách sạn, đặt phòng, thanh toán, hủy/hoàn tiền và cách dùng hệ thống. Câu này hơi ngoài phạm vi của mình. Bạn muốn mình hỗ trợ tìm nơi ở, lên lịch trình hay giải thích quy trình đặt phòng?',
                'hotels' => [],
                'mode' => 'conversation_guard',
                'intent' => $intent,
                'context_type' => $pageDetails['type'] ?? 'general',
            ];
        }

        $context = $shouldSearchHotels
            ? $this->searchHotels($message)
            : $this->emptyHotelContext();
        $context['page_details'] = $pageDetails;

        $aiAnswer = $this->askAi($message, $context, $history, $intent);

        if ($shouldSearchHotels && $context['hotels']->isEmpty()) {
            return [
                'answer' => $aiAnswer ?: 'Mình chưa tìm thấy khách sạn phù hợp trong dữ liệu Travel Mate cho tiêu chí này. Bạn thử nói rộng hơn một chút, ví dụ bỏ bớt tiện nghi, đổi khu vực, hoặc cho mình khoảng giá mong muốn nhé.',
                'hotels' => [],
                'mode' => $aiAnswer ? 'openai' : 'fallback',
                'intent' => $intent,
                'context_type' => $pageDetails['type'] ?? 'general',
            ];
        }

        $answer = $aiAnswer ?: (
            $shouldSearchHotels
                ? $this->fallbackAnswer($message, $context)
                : $this->conversationFallback($message, $intent, $pageDetails)
        );

        return [
            'answer' => $answer,
            'hotels' => $context['hotels']->map(fn (array $hotel) => [
                'name' => $hotel['name'],
                'location' => $hotel['location'],
                'rating' => $hotel['rating'],
                'min_price' => $hotel['min_price'],
                'url' => $hotel['url'],
                'thumbnail_url' => $hotel['thumbnail_url'],
                'amenities' => $hotel['amenities'],
            ])->values()->all(),
            'mode' => $aiAnswer ? 'openai' : 'fallback',
            'intent' => $intent,
            'context_type' => $pageDetails['type'] ?? 'general',
        ];
    }

    private function emptyHotelContext(): array
    {
        return [
            'matched_amenities' => [],
            'max_price' => null,
            'locations' => [],
            'hotels' => collect(),
            'page_details' => [
                'type' => 'general',
                'has_record' => false,
                'lines' => [],
            ],
        ];
    }

    private function searchHotels(string $message): array
    {
        $normalized = $this->fold($message);
        $amenities = Amenity::where('status', 'active')->get();
        $matchedAmenities = $amenities
            ->filter(fn (Amenity $amenity) => Str::contains($normalized, $this->fold($amenity->name)))
            ->values();

        $maxPrice = $this->extractMaxPrice($normalized);
        $locationNeedles = $this->extractLocations($normalized);

        $hotels = Hotel::with(['amenities', 'roomTypes' => function ($query) {
                $query->where('status', 'active')->with('amenities');
            }])
            ->where('status', 'active')
            ->get()
            ->map(function (Hotel $hotel) use ($matchedAmenities, $maxPrice, $locationNeedles, $normalized) {
                $roomTypes = $hotel->roomTypes->where('status', 'active');
                $minPrice = (float) $roomTypes->min('price_per_night');
                $hotelText = $this->fold(collect([
                    $hotel->name,
                    $hotel->address,
                    $hotel->ward,
                    $hotel->district,
                    $hotel->province,
                    $hotel->description,
                ])->filter()->implode(' '));

                $score = 0;
                $hasLocationFilter = !empty($locationNeedles);
                $locationMatched = !$hasLocationFilter || collect($locationNeedles)
                    ->contains(fn (string $needle) => Str::contains($hotelText, $needle));

                if (!$locationMatched) {
                    return null;
                }

                if ($hasLocationFilter) {
                    $score += 35;
                } elseif (Str::contains($hotelText, $normalized)) {
                    $score += 10;
                }

                foreach ($matchedAmenities as $amenity) {
                    if ($hotel->amenities->contains('id', $amenity->id)) {
                        $score += 16;
                    }
                }

                if ($maxPrice && $minPrice > 0 && $minPrice <= $maxPrice) {
                    $score += 18;
                } elseif ($maxPrice && $minPrice > $maxPrice) {
                    return null;
                }

                $score += min(15, (float) $hotel->average_rating * 3);
                $score += min(10, $roomTypes->count() * 2);

                return [
                    'name' => $hotel->name,
                    'location' => collect([$hotel->address, $hotel->ward, $hotel->district, $hotel->province])->filter()->implode(', '),
                    'rating' => (float) $hotel->average_rating,
                    'min_price' => $minPrice,
                    'url' => route('customer.hotels.show', $hotel, false),
                    'thumbnail_url' => $hotel->thumbnail ? '/storage/' . ltrim($hotel->thumbnail, '/') : null,
                    'amenities' => $hotel->amenities->pluck('name')->take(6)->values()->all(),
                    'score' => $score,
                ];
            })
            ->filter()
            ->sortByDesc('score')
            ->take(5)
            ->values();

        return [
            'matched_amenities' => $matchedAmenities->pluck('name')->values()->all(),
            'max_price' => $maxPrice,
            'locations' => $locationNeedles,
            'hotels' => $hotels,
        ];
    }

    private function askAi(string $message, array $context, array $history, string $intent): ?string
    {
        $hotelLines = $context['hotels']->isEmpty()
            ? 'Khong co danh sach khach san can goi y trong luot nay.'
            : $context['hotels']
                ->map(fn (array $hotel, int $index) => sprintf(
                    "%d. %s | %s | %.1f sao | tu %s VND/dem | tien nghi: %s | link: %s",
                    $index + 1,
                    $hotel['name'],
                    $hotel['location'],
                    $hotel['rating'],
                    number_format($hotel['min_price'], 0, ',', '.'),
                    implode(', ', $hotel['amenities']),
                    $hotel['url']
                ))
                ->implode("\n");
        $pageLines = $this->pageContextLines($context['page_details'] ?? []);

        return $this->openAi->generate(
            $this->systemInstructions(),
            "Intent: {$intent}\n\nLich su gan day:\n{$this->historyLines($history)}\n\nTin nhan hien tai: {$message}\n\nNgu canh trang hien tai va du lieu chi tiet duoc phep xem:\n{$pageLines}\n\nThong tin Travel Mate can biet:\n{$this->platformFacts()}\n\nDu lieu khach san hop le neu can goi y:\n{$hotelLines}",
            800
        );
    }

    private function fallbackAnswer(string $message, array $context): string
    {
        $topHotels = $context['hotels']->take(3);
        $lines = $topHotels->map(function (array $hotel) {
            $price = $hotel['min_price'] > 0
                ? number_format($hotel['min_price'], 0, ',', '.') . 'đ/đêm'
                : 'đang cập nhật giá';

            return "- {$hotel['name']} ({$hotel['location']}), đánh giá " . number_format($hotel['rating'], 1) . ", từ {$price}.";
        })->implode("\n");

        return "Mình tìm được vài lựa chọn phù hợp trong Travel Mate:\n{$lines}\nBạn có thể mở chi tiết từng khách sạn để kiểm tra ngày ở, hạng phòng còn trống và tiếp tục đặt phòng trên giao diện.";
    }

    private function conversationFallback(string $message, string $intent, array $pageDetails = []): string
    {
        return match ($intent) {
            'page_context' => $this->pageContextFallback($pageDetails, $message),
            'greeting' => 'Chào bạn, mình là Travel Mate AI. Mình có thể trò chuyện về chuyến đi, gợi ý khách sạn, giải thích cách đặt phòng, thanh toán, hủy/hoàn tiền hoặc hỗ trợ bạn chọn khu vực lưu trú. Bạn đang muốn đi đâu?',
            'capabilities' => "Mình có thể hỗ trợ các việc sau:\n- Trò chuyện và hỏi thêm để hiểu nhu cầu chuyến đi.\n- Gợi ý khách sạn theo địa điểm, tiện nghi, giá và đánh giá.\n- Giải thích quy trình đặt phòng, giữ phòng 15 phút và thanh toán.\n- Hướng dẫn cách hủy đơn hoặc liên hệ hỗ trợ.\n- Gợi ý lịch trình nhẹ theo điểm đến.\nMình chỉ tư vấn, không tự đặt phòng, thanh toán hoặc hủy đơn thay bạn.",
            'booking_process' => "Quy trình đặt phòng trên Travel Mate là: tìm khách sạn, chọn ngày ở và số khách, mở chi tiết khách sạn, chọn hạng phòng, nhập thông tin liên hệ, tạo đơn rồi thanh toán trong 15 phút. Khi thanh toán thành công, đơn chuyển sang đã xác nhận.",
            'payment_help' => "Ở bản demo này bạn có hai lựa chọn thanh toán: VNPAY sandbox hoặc thanh toán giả lập cho demo. Sau khi thanh toán thành công, hệ thống ghi nhận trạng thái thanh toán và xác nhận booking. Nếu quá hạn giữ phòng, đơn có thể cần Travel Mate kiểm tra thủ công trước khi xác nhận lưu trú.",
            'cancellation_help' => "Nếu đơn chưa thanh toán và còn trước ngày nhận phòng, bạn có thể hủy ngay trong lịch sử đặt phòng. Nếu đơn đã thanh toán và còn trước ngày nhận phòng, hệ thống gửi yêu cầu hủy/hoàn tiền để Admin kiểm tra theo chính sách khách sạn. Từ ngày nhận phòng trở đi, bạn cần liên hệ Admin Travel Mate hoặc khách sạn để được hỗ trợ.",
            'partner_help' => "Nếu bạn là Customer và muốn đưa khách sạn lên Travel Mate, hãy gửi yêu cầu trở thành đối tác trong tài khoản. Admin sẽ xét duyệt trước khi tài khoản được chuyển sang Owner để quản lý khách sạn, phòng, check-in và check-out.",
            'support' => "Bạn có thể liên hệ Travel Mate qua hotline 1900 9999 hoặc email support@travelmate.local. Khi hỏi về một booking, hãy chuẩn bị mã đơn để Admin kiểm tra nhanh hơn.",
            'itinerary' => $this->itineraryFallback($message),
            default => 'Mình hiểu. Bạn có thể nói tự nhiên hơn một chút, ví dụ: “mình đi Đà Nẵng 2 ngày, thích gần biển và có hồ bơi” hoặc “đơn đã thanh toán thì hủy thế nào?”. Mình sẽ tư vấn theo hướng phù hợp nhất.',
        };
    }

    private function pageContextLines(array $pageDetails): string
    {
        $lines = $pageDetails['lines'] ?? [];

        if (empty($lines)) {
            return 'Khong co ngu canh trang chi tiet cho luot hoi nay.';
        }

        return collect($lines)
            ->filter()
            ->map(fn (string $line) => '- ' . $line)
            ->implode("\n");
    }

    private function pageContextFallback(array $pageDetails, string $message = ''): string
    {
        $directAnswer = $this->directPageContextAnswer($pageDetails, $message);
        if ($directAnswer) {
            return $directAnswer;
        }

        $lines = $this->focusedPageLines($pageDetails, $message);

        if ($lines->isEmpty()) {
            $lines = collect($pageDetails['lines'] ?? [])
                ->filter()
                ->skip(3)
                ->take(8)
                ->values();
        }

        if ($lines->isEmpty()) {
            return 'Mình chưa có đủ dữ liệu chi tiết của trang hiện tại. Bạn có thể hỏi rõ hơn, ví dụ: "khách sạn này có hạng phòng nào?", "booking này đang ở trạng thái gì?", hoặc "trang thanh toán này cần làm gì tiếp?".';
        }

        return "Mình đang đọc được chi tiết của phần này trong Travel Mate:\n- "
            . $lines->implode("\n- ")
            . "\nBạn có thể hỏi tiếp sâu hơn về trạng thái, hành động tiếp theo, phòng, thanh toán, hoàn tiền hoặc dữ liệu vận hành của phần đang mở.";
    }

    private function directPageContextAnswer(array $pageDetails, string $message): ?string
    {
        $text = $this->fold($message);
        $type = $pageDetails['type'] ?? 'general';
        $lines = collect($pageDetails['lines'] ?? [])->filter()->values();

        if (Str::contains($text, ['may hang phong', 'bao nhieu hang phong', 'co nhung hang phong nao', 'cac hang phong', 'hang phong nao'])) {
            $roomLine = $lines->first(fn (string $line) => Str::startsWith($this->fold($line), 'hang phong:'));

            if ($roomLine) {
                $rawRoomText = trim((string) Str::of($roomLine)->after(':')->trim()->rtrim('.'));
                $roomTypes = collect(explode(' | ', $rawRoomText))
                    ->map(fn (string $item) => trim($item))
                    ->filter()
                    ->values();

                if ($roomTypes->isNotEmpty()) {
                    return 'Khách sạn này hiện có ' . $roomTypes->count() . " hạng phòng:\n- "
                        . $roomTypes->implode("\n- ");
                }
            }

            if ($type === 'hotel_search') {
                return 'Bạn đang ở trang danh sách khách sạn nên mình chưa có một khách sạn cụ thể để đếm hạng phòng. Hãy mở trang chi tiết của một khách sạn, rồi hỏi lại “có mấy hạng phòng?” để mình trả lời đúng khách sạn đó.';
            }
        }

        return null;
    }

    private function focusedPageLines(array $pageDetails, string $message)
    {
        $text = $this->fold($message);
        $lines = collect($pageDetails['lines'] ?? [])
            ->filter()
            ->skip(3)
            ->values();

        if ($lines->isEmpty()) {
            return $lines;
        }

        $lineMatches = function (array $keywords) use ($lines) {
            return $lines
                ->filter(function (string $line) use ($keywords) {
                    $folded = $this->fold($line);

                    return collect($keywords)->contains(fn (string $keyword) => Str::contains($folded, $keyword));
                })
                ->take(8)
                ->values();
        };

        if (Str::contains($text, ['tien nghi', 'co gi', 'dich vu', 'ho boi', 'spa', 'wifi'])) {
            return $lineMatches(['tien nghi', 'mo ta', 'hang phong']);
        }

        if (Str::contains($text, ['phong', 'hang phong', 'gia bao nhieu', 'gia phong', 'muc gia', 'bao nhieu tien', 'bao nhieu', 'con phong', 'dat phong'])) {
            return $lineMatches(['hang phong', 'vnd', '/dem', 'phong san sang', 'tong tien', 'thanh toan']);
        }

        if (Str::contains($text, ['dia chi', 'vi tri', 'ban do', 'toa do', 'o dau', 'gan dau'])) {
            return $lineMatches(['dia chi', 'toa do', 'khach san']);
        }

        if (Str::contains($text, ['danh gia', 'review', 'sao', 'nhan xet'])) {
            return $lineMatches(['danh gia:', 'tom tat', 'danh gia gan day']);
        }

        if (Str::contains($text, ['huy', 'hoan tien', 'chinh sach'])) {
            return $lineMatches(['chinh sach huy', 'hoan tien', 'thanh toan', 'booking']);
        }

        return collect();
    }

    private function itineraryFallback(string $message): string
    {
        $locations = $this->extractLocations($this->fold($message));
        $place = $locations[0] ?? 'điểm đến của bạn';

        return "Được, mình có thể gợi ý lịch trình cơ bản cho {$place}. Một khung dễ dùng là:\n- Buổi sáng: chọn điểm tham quan chính gần nơi lưu trú.\n- Buổi trưa: ăn uống và nghỉ nhẹ để tránh di chuyển quá nhiều.\n- Buổi chiều: đi điểm ngắm cảnh, biển, phố cổ hoặc khu trung tâm.\n- Buổi tối: chọn khu ăn uống/đi dạo gần khách sạn.\nNếu bạn cho mình số ngày, ngân sách, thích biển/núi/trung tâm/yên tĩnh, mình sẽ gợi ý cụ thể hơn và kèm khách sạn phù hợp trong Travel Mate.";
    }

    private function detectIntent(string $message): string
    {
        $text = $this->fold($message);

        if (Str::of($text)->trim()->length() < 2) {
            return 'off_topic';
        }

        if (Str::contains($text, ['xin chao', 'chao ban', 'hello', 'hey']) || preg_match('/(^|\s)hi($|\s)/', $text)) {
            return 'greeting';
        }

        if (Str::contains($text, ['ban lam duoc gi', 'co the lam gi', 'co the giup minh lam gi', 'giup minh lam gi', 'ban giup duoc gi', 'lam duoc gi', 'tro giup', 'huong dan', 'help', 'chat', 'noi chuyen', 'tu van duoc gi'])) {
            return 'capabilities';
        }

        if (Str::contains($text, ['trang nay', 'phan nay', 'muc nay', 'o day', 'dang xem', 'chi tiet nay', 'khach san nay', 'booking nay', 'don nay', 'phong nay', 'hang phong nay', 'nut nay', 'giai thich trang', 'giai thich phan'])) {
            return 'page_context';
        }

        if (Str::contains($text, ['huy', 'hoan tien', 'refund', 'khong hoan', 'doi lich'])) {
            return 'cancellation_help';
        }

        if (Str::contains($text, ['thanh toan', 'vnpay', 'sandbox', 'demo', 'gia lap', 'the test', 'otp', 'tra tien'])) {
            return 'payment_help';
        }

        if (Str::contains($text, ['dat phong', 'giu phong', 'tao don', 'booking', 'nhan phong', 'tra phong', 'check in', 'check out', 'con phong'])) {
            return $this->containsExplicitHotelSearchCue($text) ? 'hotel_search' : 'booking_process';
        }

        if (Str::contains($text, ['doi tac', 'owner', 'chu khach san', 'dang khach san', 'quan ly khach san'])) {
            return 'partner_help';
        }

        if (Str::contains($text, ['hotline', 'email', 'lien he', 'ho tro', 'admin', 'support'])) {
            return 'support';
        }

        if (Str::contains($text, ['lich trinh', 'du lich', 'di dau', 'choi gi', 'an gi', 'kinh nghiem', 'ngay', 'dem'])) {
            return $this->containsLodgingSearchCue($text) ? 'hotel_search' : 'itinerary';
        }

        if ($this->containsHotelSearchCue($text)) {
            return 'hotel_search';
        }

        return 'off_topic';
    }

    private function shouldUseCurrentPageContext(string $message, string $intent, array $pageDetails): bool
    {
        if (empty($pageDetails['has_record'])) {
            return false;
        }

        $type = $pageDetails['type'] ?? 'general';
        $text = $this->fold($message);
        $detailTypes = [
            'hotel_detail',
            'owner_hotel_detail',
            'admin_hotel_detail',
            'customer_booking_detail',
            'customer_checkout',
            'owner_booking_detail',
            'owner_room_type_detail',
            'owner_room_detail',
            'owner_revenue',
            'admin_refund_detail',
            'admin_settlement_detail',
            'admin_user_detail',
            'admin_partner_request_detail',
        ];

        if (!in_array($type, $detailTypes, true)) {
            return false;
        }

        if (Str::contains($text, [
            'trang nay',
            'phan nay',
            'muc nay',
            'o day',
            'dang xem',
            'chi tiet nay',
            'khach san nay',
            'booking nay',
            'don nay',
            'phong nay',
            'hang phong nay',
            'nut nay',
        ])) {
            return true;
        }

        $explicitSearch = Str::contains($text, [
            'tim khach san',
            'goi y khach san',
            'khach san nao',
            'noi o nao',
            'tim noi o',
            'khach san khac',
            'goi y them',
            'tim them',
        ]);

        if ($explicitSearch) {
            return false;
        }

        if (Str::contains($type, 'hotel') && Str::contains($text, [
            'khach san',
            'tien nghi',
            'dich vu',
            'phong',
            'hang phong',
            'gia',
            'bao nhieu',
            'dia chi',
            'vi tri',
            'ban do',
            'toa do',
            'danh gia',
            'review',
            'chinh sach',
            'huy',
            'checkin',
            'checkout',
            'nhan phong',
            'tra phong',
        ])) {
            return true;
        }

        return $intent === 'hotel_search' && Str::contains($type, 'hotel');
    }

    private function shouldSearchHotels(string $message, string $intent): bool
    {
        if ($intent === 'hotel_search') {
            return true;
        }

        return false;
    }

    private function containsHotelSearchCue(string $normalized): bool
    {
        if ($this->containsExplicitHotelSearchCue($normalized)) {
            return true;
        }

        $keywords = [
            'khach san', 'hotel', 'resort', 'homestay', 'noi o',
            'luu tru', 'gan bien', 'gan trung tam', 'gia duoi', 'duoi',
            'toi da', 'ho boi', 'spa', 'wifi', 'view', 'gan',
        ];

        if (collect($keywords)->contains(fn (string $keyword) => Str::contains($normalized, $keyword))) {
            return true;
        }

        if ($this->extractMaxPrice($normalized)) {
            return true;
        }

        if (!empty($this->extractLocations($normalized))) {
            return true;
        }

        return Amenity::where('status', 'active')
            ->pluck('name')
            ->contains(fn (string $name) => Str::contains($normalized, $this->fold($name)));
    }

    private function containsExplicitHotelSearchCue(string $normalized): bool
    {
        return Str::contains($normalized, [
            'tim khach san',
            'tim hotel',
            'tim resort',
            'tim homestay',
            'goi y khach san',
            'goi y hotel',
            'goi y resort',
            'khach san nao',
            'hotel nao',
            'noi o nao',
            'tim noi o',
            'khach san khac',
            'goi y them',
            'tim them',
        ]);
    }

    private function containsLodgingSearchCue(string $normalized): bool
    {
        return Str::contains($normalized, [
            'khach san',
            'hotel',
            'resort',
            'homestay',
            'noi o',
            'luu tru',
            'hang phong',
            'dat phong',
            'tim phong',
            'tim noi o',
        ]);
    }

    private function sanitizeHistory(array $history): array
    {
        return collect($history)
            ->filter(fn ($item) => is_array($item))
            ->map(function (array $item) {
                $role = $item['role'] ?? null;
                $content = trim(strip_tags((string) ($item['content'] ?? '')));

                if (!in_array($role, ['user', 'assistant'], true) || $content === '') {
                    return null;
                }

                return [
                    'role' => $role,
                    'content' => Str::limit($content, 600, ''),
                ];
            })
            ->filter()
            ->take(-8)
            ->values()
            ->all();
    }

    private function historyLines(array $history): string
    {
        if (empty($history)) {
            return 'Chua co lich su hoi thoai.';
        }

        return collect($history)
            ->map(fn (array $item) => ($item['role'] === 'user' ? 'Nguoi dung: ' : 'Travel Mate AI: ') . $item['content'])
            ->implode("\n");
    }

    private function systemInstructions(): string
    {
        return implode("\n", [
            'Ban la Travel Mate AI, tro ly hoi thoai tieng Viet cho website dat phong khach san Travel Mate.',
            'Hay tro chuyen tu nhien, hoi lai khi thieu thong tin, va uu tien cau tra loi ngan gon, huu ich.',
            'Duoc tu van du lich, lich trinh co ban, cach dung he thong, dat phong, thanh toan, huy/hoan tien va goi y khach san.',
            'Khi co ngu canh trang hien tai, hay uu tien tra loi sau ve record dang mo: khach san, booking, checkout, refund, settlement, room, room type, user hoac revenue.',
            'Neu ngu canh trang khong co thong tin nguoi dung hoi, noi ro la hien tai chua co du lieu thay vi tu bia.',
            'Chi neu ten khach san, gia, danh gia, tien nghi khi chung xuat hien trong du lieu khach san hop le duoc cung cap.',
            'Khong tu dat phong, khong huy don, khong thanh toan thay nguoi dung, khong hua chac phong neu nguoi dung chua bam dat tren giao dien.',
            'Khong tiet lo ti le chia doanh thu, phi noi bo, cong no noi bo, bang giao dich tai chinh noi bo hoac thong tin van hanh khong danh cho khach hang.',
            'Neu cau hoi ngoai Travel Mate/du lich/khach san, hay lich su chuyen huong ve viec tim noi o, len lich trinh hoac dung he thong.',
        ]);
    }

    private function platformFacts(): string
    {
        return implode("\n", [
            '- Guest co the xem trang chu, danh sach khach san va chi tiet khach san.',
            '- Customer co the dat phong, thanh toan, xem lich su, huy khi du dieu kien, danh gia don da hoan tat va gui yeu cau doi tac.',
            '- Owner/Admin co the xem trang cong khai nhung khong duoc dat phong tren giao dien customer.',
            '- Don pending_payment giu phong 15 phut. Het han thi phong duoc mo lai.',
            '- Thanh toan demo ho tro VNPAY sandbox va thanh toan gia lap.',
            '- Don chua thanh toan va con truoc ngay nhan phong co the huy ngay, khong phat sinh hoan tien.',
            '- Don da thanh toan va con truoc ngay nhan phong se gui yeu cau huy/hoan tien de Admin xem xet theo chinh sach khach san.',
            '- Den ngay nhan phong hoac sau do, khach can lien he Admin Travel Mate hoac khach san de duoc ho tro.',
            '- Hotline Travel Mate: 1900 9999. Email: support@travelmate.local.',
        ]);
    }

    private function extractLocations(string $normalized): array
    {
        $knownLocations = Hotel::where('status', 'active')
            ->get(['province', 'district', 'ward', 'address', 'name'])
            ->flatMap(fn (Hotel $hotel) => [$hotel->province, $hotel->district, $hotel->ward, $hotel->address, $hotel->name])
            ->filter()
            ->map(fn (string $value) => $this->fold($value))
            ->filter(fn (string $value) => Str::length($value) >= 3)
            ->unique()
            ->values();

        return $knownLocations
            ->filter(fn (string $location) => Str::contains($normalized, $location))
            ->values()
            ->all();
    }

    private function extractMaxPrice(string $normalized): ?float
    {
        if (!preg_match('/(?:duoi|toi da|nho hon|<=)\s*([0-9]+(?:[.,][0-9]+)?)(\s*trieu|\s*k|\s*nghin)?/u', $normalized, $matches)) {
            return null;
        }

        $number = (float) str_replace(',', '.', $matches[1]);
        $unit = trim($matches[2] ?? '');

        if (Str::contains($unit, 'trieu')) {
            return $number * 1000000;
        }

        if (Str::contains($unit, 'k') || Str::contains($unit, 'nghin')) {
            return $number * 1000;
        }

        return $number >= 10000 ? $number : $number * 1000000;
    }

    private function fold(string $value): string
    {
        $value = Str::ascii($value);

        return Str::of($value)
            ->lower()
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->toString();
    }
}
