<?php

namespace App\Services\Ai;

use App\Models\Hotel;
use App\Models\HotelReviewSummary;
use App\Models\Review;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ReviewSummaryService
{
    public function __construct(private OpenAiTextService $openAi)
    {
    }

    public function summaryForHotel(Hotel $hotel): ?HotelReviewSummary
    {
        $reviews = $this->visibleReviews($hotel);
        $minimum = max(1, (int) config('travelmate_ai.review_min_count', 1));

        if ($reviews->count() < $minimum) {
            return $hotel->reviewSummary;
        }

        $hash = $this->reviewsHash($reviews);
        $existing = $hotel->reviewSummary;

        if ($existing && $existing->reviews_hash === $hash) {
            return $existing;
        }

        return $this->refresh($hotel, $reviews, $hash);
    }

    public function refreshIfEligible(Hotel $hotel, bool $force = false): ?HotelReviewSummary
    {
        $hotel->loadMissing('reviewSummary');
        $reviews = $this->visibleReviews($hotel);
        $minimum = max(1, (int) config('travelmate_ai.review_min_count', 1));

        if ($reviews->count() < $minimum) {
            return $hotel->reviewSummary;
        }

        $hash = $this->reviewsHash($reviews);
        $existing = $hotel->reviewSummary;

        if (!$force && $existing && $existing->reviews_hash === $hash) {
            return $existing;
        }

        if (!$force && $existing) {
            $newCount = max(0, $reviews->count() - (int) $existing->review_count);
            $step = max(1, (int) config('travelmate_ai.review_refresh_step', 10));
            if ($newCount > 0 && $newCount < $step) {
                return $existing;
            }
        }

        return $this->refresh($hotel, $reviews, $hash);
    }

    private function refresh(Hotel $hotel, Collection $reviews, string $hash): HotelReviewSummary
    {
        $fallback = $this->fallbackSummary($reviews);
        $aiPayload = $this->askAi($hotel, $reviews);

        $payload = is_array($aiPayload) && filled($aiPayload['summary'] ?? null)
            ? [
                'summary' => (string) $aiPayload['summary'],
                'pros' => array_values(array_filter((array) ($aiPayload['pros'] ?? []))),
                'cons' => array_values(array_filter((array) ($aiPayload['cons'] ?? []))),
                'generated_by' => 'openai',
            ]
            : array_merge($fallback, ['generated_by' => 'fallback']);

        return HotelReviewSummary::updateOrCreate(
            ['hotel_id' => $hotel->id],
            [
                'summary' => Str::limit($payload['summary'], 2000, ''),
                'pros' => array_slice($payload['pros'], 0, 5),
                'cons' => array_slice($payload['cons'], 0, 5),
                'review_count' => $reviews->count(),
                'reviews_hash' => $hash,
                'generated_by' => $payload['generated_by'],
                'generated_at' => now(),
            ]
        );
    }

    private function askAi(Hotel $hotel, Collection $reviews): ?array
    {
        $reviewLines = $reviews->take(30)->map(function (Review $review) {
            return sprintf(
                "- %d/5: %s",
                (int) $review->rating,
                Str::limit((string) $review->comment, 500, '')
            );
        })->implode("\n");

        return $this->openAi->generateJson(
            'Ban tom tat review khach san cho Travel Mate. Chi dung review cong khai duoc cung cap. Khong bia them. Tra ve JSON gom summary, pros, cons. Pros va cons la mang chuoi ngan gon tieng Viet.',
            "Khach san: {$hotel->name}\nDia chi: {$hotel->address}, {$hotel->district}, {$hotel->province}\nReview cong khai:\n{$reviewLines}",
            900
        );
    }

    private function fallbackSummary(Collection $reviews): array
    {
        $average = round((float) $reviews->avg('rating'), 1);
        $comments = $reviews->pluck('comment')->filter()->map(fn ($comment) => Str::of(Str::ascii($comment))->lower()->toString());
        $joined = $comments->implode(' ');

        $pros = [];
        $cons = [];

        $positiveMap = [
            'vi tri' => 'Vị trí được khách đánh giá thuận tiện.',
            'sach' => 'Không gian/phòng được ghi nhận sạch sẽ.',
            'dep' => 'Thiết kế và không gian lưu trú tạo ấn tượng tốt.',
            'nhan vien' => 'Nhân viên/dịch vụ được phản hồi tích cực.',
            'thanh toan' => 'Quy trình đặt phòng và thanh toán rõ ràng.',
            'phong' => 'Hạng phòng đáp ứng tốt nhu cầu lưu trú.',
        ];

        $negativeMap = [
            'on' => 'Có phản hồi cần lưu ý về tiếng ồn.',
            'cham' => 'Một số trải nghiệm có thể chậm hơn kỳ vọng.',
            'xa' => 'Vị trí có thể chưa phù hợp với khách muốn ở trung tâm.',
            'nho' => 'Diện tích/phòng có thể chưa rộng với một số khách.',
            'dat' => 'Mức giá có thể là điểm cân nhắc.',
            'ban' => 'Có phản hồi cần cải thiện về vệ sinh.',
        ];

        foreach ($positiveMap as $keyword => $sentence) {
            if (Str::contains($joined, $keyword)) {
                $pros[] = $sentence;
            }
        }

        foreach ($negativeMap as $keyword => $sentence) {
            if (Str::contains($joined, $keyword)) {
                $cons[] = $sentence;
            }
        }

        if (empty($pros)) {
            $pros[] = $average >= 4
                ? 'Tổng thể khách hàng đang đánh giá tích cực về trải nghiệm lưu trú.'
                : 'Khách sạn đã có phản hồi thực tế để khách tham khảo trước khi đặt.';
        }

        if (empty($cons)) {
            $cons[] = 'Chưa ghi nhận nhược điểm nổi bật từ các đánh giá công khai hiện có.';
        }

        return [
            'summary' => "Dựa trên {$reviews->count()} đánh giá công khai, khách sạn đạt điểm trung bình {$average}/5. Nhìn chung, phản hồi hiện tại cho thấy trải nghiệm lưu trú phù hợp để khách tham khảo trước khi đặt phòng.",
            'pros' => $pros,
            'cons' => $cons,
        ];
    }

    private function visibleReviews(Hotel $hotel): Collection
    {
        return $hotel->reviews()
            ->where('status', 'visible')
            ->whereNotNull('comment')
            ->latest()
            ->get();
    }

    private function reviewsHash(Collection $reviews): string
    {
        return sha1($reviews->map(fn (Review $review) => implode('|', [
            $review->id,
            $review->rating,
            $review->comment,
            optional($review->updated_at)->timestamp,
        ]))->implode('||'));
    }
}
