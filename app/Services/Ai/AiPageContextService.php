<?php

namespace App\Services\Ai;

use App\Models\Booking;
use App\Models\FinancialTransaction;
use App\Models\Hotel;
use App\Models\OwnerAdjustment;
use App\Models\PartnerRequest;
use App\Models\Payment;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Support\Str;

class AiPageContextService
{
    public function build(array $input, ?User $viewer): array
    {
        $page = $this->normalize($input);
        $role = $viewer?->role ?? 'guest';
        $lines = [
            'Trang hien tai: ' . ($page['title'] ?: 'Khong ro'),
            'Duong dan hien tai: ' . ($page['path'] ?: '/'),
            'Vai tro dang xem: ' . $role,
        ];

        $type = 'general';
        $hasRecord = false;
        $path = $page['path'];

        if ($path === '/' || $path === '/home') {
            $type = 'travel_home';
            $hasRecord = true;
            $lines = array_merge($lines, $this->homeLines());
        } elseif ($path === '/hotels') {
            $type = 'hotel_search';
            $hasRecord = true;
            $lines = array_merge($lines, $this->hotelSearchLines());
        } elseif (preg_match('#^/hotels/(\d+)$#', $path, $matches)) {
            $hotel = Hotel::find((int) $matches[1]);
            if ($hotel && $this->canViewHotel($hotel, $viewer)) {
                $type = 'hotel_detail';
                $hasRecord = true;
                $lines = array_merge($lines, $this->hotelLines($hotel, $viewer));
            }
        } elseif (preg_match('#^/payments/(\d+)/checkout$#', $path, $matches)) {
            $booking = Booking::find((int) $matches[1]);
            if ($booking && $this->canViewBooking($booking, $viewer)) {
                $type = 'customer_checkout';
                $hasRecord = true;
                $lines = array_merge($lines, $this->bookingLines($booking, $viewer, 'checkout'));
            }
        } elseif (preg_match('#^/my/bookings/(\d+)$#', $path, $matches)) {
            $booking = Booking::find((int) $matches[1]);
            if ($booking && $this->canViewBooking($booking, $viewer)) {
                $type = 'customer_booking_detail';
                $hasRecord = true;
                $lines = array_merge($lines, $this->bookingLines($booking, $viewer, 'customer'));
            }
        } elseif ($path === '/my/bookings') {
            $type = 'customer_booking_history';
            $hasRecord = $viewer?->isCustomer() ?? false;
            $lines = array_merge($lines, $this->customerBookingHistoryLines($viewer));
        } elseif (preg_match('#^/owner/bookings/(\d+)#', $path, $matches)) {
            $booking = Booking::find((int) $matches[1]);
            if ($booking && $this->canViewBooking($booking, $viewer)) {
                $type = 'owner_booking_detail';
                $hasRecord = true;
                $lines = array_merge($lines, $this->bookingLines($booking, $viewer, 'owner'));
            }
        } elseif (preg_match('#^/owner/hotels/(\d+)#', $path, $matches)) {
            $hotel = Hotel::find((int) $matches[1]);
            if ($hotel && $this->canViewHotel($hotel, $viewer)) {
                $type = 'owner_hotel_detail';
                $hasRecord = true;
                $lines = array_merge($lines, $this->hotelLines($hotel, $viewer));
            }
        } elseif (preg_match('#^/owner/room-types/(\d+)#', $path, $matches)) {
            $roomType = RoomType::with(['hotel', 'amenities', 'rooms'])->find((int) $matches[1]);
            if ($roomType && $this->canViewHotel($roomType->hotel, $viewer)) {
                $type = 'owner_room_type_detail';
                $hasRecord = true;
                $lines = array_merge($lines, $this->roomTypeLines($roomType));
            }
        } elseif (preg_match('#^/owner/rooms/(\d+)#', $path, $matches)) {
            $room = Room::with(['hotel', 'roomType'])->find((int) $matches[1]);
            if ($room && $this->canViewHotel($room->hotel, $viewer)) {
                $type = 'owner_room_detail';
                $hasRecord = true;
                $lines = array_merge($lines, $this->roomLines($room));
            }
        } elseif ($path === '/owner/revenues') {
            $type = 'owner_revenue';
            $hasRecord = $viewer?->isOwner() ?? false;
            $lines = array_merge($lines, $this->ownerRevenueLines($viewer));
        } elseif (preg_match('#^/admin/refunds/(\d+)$#', $path, $matches)) {
            $payment = Payment::find((int) $matches[1]);
            if ($payment && $viewer?->isAdmin()) {
                $type = 'admin_refund_detail';
                $hasRecord = true;
                $lines = array_merge($lines, $this->refundLines($payment));
            }
        } elseif (preg_match('#^/admin/settlements/(\d+)$#', $path, $matches)) {
            $transaction = FinancialTransaction::find((int) $matches[1]);
            if ($transaction && $viewer?->isAdmin()) {
                $type = 'admin_settlement_detail';
                $hasRecord = true;
                $lines = array_merge($lines, $this->settlementLines($transaction));
            }
        } elseif (preg_match('#^/admin/users/(\d+)$#', $path, $matches)) {
            $user = User::find((int) $matches[1]);
            if ($user && $viewer?->isAdmin()) {
                $type = 'admin_user_detail';
                $hasRecord = true;
                $lines = array_merge($lines, $this->userLines($user));
            }
        } elseif (preg_match('#^/admin/hotels/(\d+)#', $path, $matches)) {
            $hotel = Hotel::find((int) $matches[1]);
            if ($hotel && $viewer?->isAdmin()) {
                $type = 'admin_hotel_detail';
                $hasRecord = true;
                $lines = array_merge($lines, $this->hotelLines($hotel, $viewer));
            }
        } elseif (preg_match('#^/admin/partner-requests/(\d+)$#', $path, $matches)) {
            $request = PartnerRequest::with(['user', 'reviewer'])->find((int) $matches[1]);
            if ($request && $viewer?->isAdmin()) {
                $type = 'admin_partner_request_detail';
                $hasRecord = true;
                $lines = array_merge($lines, $this->partnerRequestLines($request));
            }
        }

        return [
            'type' => $type,
            'path' => $page['path'],
            'title' => $page['title'],
            'has_record' => $hasRecord,
            'lines' => collect($lines)->filter()->take(40)->values()->all(),
        ];
    }

    private function normalize(array $input): array
    {
        $url = trim(strip_tags((string) ($input['url'] ?? '')));
        $path = trim(strip_tags((string) ($input['path'] ?? '')));

        if ($url !== '') {
            $parsedPath = parse_url($url, PHP_URL_PATH);
            if (is_string($parsedPath) && $parsedPath !== '') {
                $path = $parsedPath;
            }
        }

        if ($path === '') {
            $path = '/';
        }

        return [
            'url' => Str::limit($url, 1000, ''),
            'path' => '/' . ltrim($path, '/'),
            'title' => Str::limit(trim(strip_tags((string) ($input['title'] ?? ''))), 160, ''),
        ];
    }

    private function homeLines(): array
    {
        $activeHotels = Hotel::where('status', 'active')->count();
        $featured = Hotel::where('status', 'active')
            ->orderByDesc('average_rating')
            ->take(3)
            ->pluck('name')
            ->implode(', ');

        return [
            "Tong quan he thong: {$activeHotels} khach san dang cong khai.",
            $featured ? "Khach san noi bat: {$featured}." : null,
            'Trang nay phu hop de tim diem den, xem khach san noi bat va bat dau quy trinh dat phong.',
        ];
    }

    private function hotelSearchLines(): array
    {
        $activeHotels = Hotel::where('status', 'active')->count();
        $locations = Hotel::where('status', 'active')
            ->pluck('province')
            ->filter()
            ->unique()
            ->take(8)
            ->implode(', ');

        return [
            "Danh sach tim kiem co {$activeHotels} khach san dang cong khai.",
            $locations ? "Khu vuc co du lieu: {$locations}." : null,
            'Nguoi dung co the loc theo dia diem, ngay o, so khach, tien nghi va mo chi tiet tung khach san.',
        ];
    }

    private function hotelLines(Hotel $hotel, ?User $viewer): array
    {
        $hotel->load([
            'owner',
            'amenities',
            'roomTypes.amenities',
            'roomTypes.rooms',
            'reviews.reply',
            'reviewSummary',
        ]);

        $isOperator = $viewer && ($viewer->isAdmin() || ($viewer->isOwner() && (int) $hotel->owner_id === (int) $viewer->id));
        $roomLines = $hotel->roomTypes
            ->take(8)
            ->map(function (RoomType $roomType) {
                $availableRooms = $roomType->rooms->where('status', 'available')->count();
                $amenities = $roomType->amenities->pluck('name')->take(5)->implode(', ');

                return sprintf(
                    '%s: %s khach, giuong %s, %sm2, %s/dem, %s phong san sang%s',
                    $roomType->name,
                    $roomType->max_guests,
                    $roomType->bed_type ?: 'dang cap nhat',
                    $roomType->area ?: '0',
                    $this->money($roomType->price_per_night),
                    $availableRooms,
                    $amenities ? ", tien nghi {$amenities}" : ''
                );
            })
            ->implode(' | ');

        $reviewLines = $hotel->reviews
            ->where('status', 'visible')
            ->sortByDesc('created_at')
            ->take(3)
            ->map(fn ($review) => "{$review->rating} sao: " . Str::limit((string) $review->comment, 120))
            ->implode(' | ');

        $lines = [
            "Khach san: {$hotel->name}.",
            'Dia chi: ' . collect([$hotel->address, $hotel->ward, $hotel->district, $hotel->province])->filter()->implode(', ') . '.',
            $hotel->description ? 'Mo ta: ' . Str::limit($hotel->description, 260) : null,
            "Danh gia: {$hotel->average_rating}/5 tu {$hotel->review_count} danh gia.",
            $hotel->amenities->isNotEmpty() ? 'Tien nghi khach san: ' . $hotel->amenities->pluck('name')->take(12)->implode(', ') . '.' : null,
            $roomLines ? "Hang phong: {$roomLines}." : 'Khach san chua co hang phong dang ban.',
            $hotel->cancellation_policy ? 'Chinh sach huy: ' . Str::limit($hotel->cancellation_policy, 260) : null,
            "Nhan phong: " . $this->time($hotel->checkin_time) . ', tra phong: ' . $this->time($hotel->checkout_time) . '.',
            $hotel->latitude && $hotel->longitude ? "Toa do ban do: {$hotel->latitude}, {$hotel->longitude}." : null,
            $hotel->reviewSummary?->summary ? 'Tom tat danh gia AI: ' . Str::limit($hotel->reviewSummary->summary, 260) : null,
            $reviewLines ? "Danh gia gan day: {$reviewLines}." : null,
        ];

        if ($isOperator) {
            $roomStatus = $hotel->rooms()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->map(fn ($total, $status) => "{$status}: {$total}")
                ->implode(', ');

            $bookingStatus = $hotel->bookings()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->map(fn ($total, $status) => "{$status}: {$total}")
                ->implode(', ');

            $lines[] = "Trang thai kiem duyet: {$hotel->status}" . ($hotel->status_reason ? " ({$hotel->status_reason})" : '') . '.';
            $lines[] = $hotel->owner ? "Owner: {$hotel->owner->name} - {$hotel->owner->email}." : null;
            $lines[] = $roomStatus ? "Trang thai phong vat ly: {$roomStatus}." : null;
            $lines[] = $bookingStatus ? "Thong ke booking theo trang thai: {$bookingStatus}." : null;
        }

        return $lines;
    }

    private function bookingLines(Booking $booking, ?User $viewer, string $scope): array
    {
        $booking->load([
            'customer',
            'hotel.owner',
            'roomTypes.roomType',
            'payment',
            'financialTransaction.settlement',
            'roomAssignments.room',
            'review',
        ]);

        $isOperator = $viewer && ($viewer->isAdmin() || ($viewer->isOwner() && (int) $booking->hotel?->owner_id === (int) $viewer->id));
        $isCustomer = $viewer && (int) $booking->customer_id === (int) $viewer->id;
        $roomLines = $booking->roomTypes
            ->map(fn ($item) => sprintf(
                '%s x %s dem, %s, thanh tien %s',
                $item->quantity . ' ' . ($item->roomType?->name ?? 'hang phong'),
                $item->nights,
                $this->money($item->price_per_night) . '/dem',
                $this->money($item->subtotal)
            ))
            ->implode(' | ');

        $lines = [
            "Booking {$booking->booking_code}: {$this->bookingStatus($booking->status)}.",
            $booking->hotel ? "Khach san: {$booking->hotel->name}." : null,
            "Ngay o: {$this->date($booking->checkin_date)} den {$this->date($booking->checkout_date)}.",
            "So khach: {$booking->guest_count}. Tong tien khach thay: {$this->money($booking->total_amount)}.",
            $roomLines ? "Chi tiet hang phong: {$roomLines}." : null,
            $booking->special_request ? 'Yeu cau dac biet: ' . Str::limit($booking->special_request, 180) : null,
            $booking->payment ? "Thanh toan: {$booking->payment->method}, trang thai {$this->paymentStatus($booking->payment->status)}, so tien {$this->money($booking->payment->amount)}." : 'Chua co ban ghi thanh toan.',
            $booking->payment && in_array($booking->payment->status, ['refunding', 'refunded', 'non_refundable'], true)
                ? 'Hoan tien: ' . $this->money($booking->payment->refund_amount) . ($booking->payment->refund_note ? '. Ghi chu: ' . Str::limit($booking->payment->refund_note, 180) : '')
                : null,
            "Lien he Travel Mate: 1900 9999, support@travelmate.local. Khi lien he hay cung cap ma {$booking->booking_code}.",
        ];

        if ($isCustomer || $isOperator) {
            $lines[] = "Nguoi lien he: {$booking->contact_name}, {$booking->contact_phone}, {$booking->contact_email}.";
        }

        if ($booking->status === 'pending_payment') {
            $lines[] = 'Booking dang cho thanh toan; neu con trong han giu phong thi customer co the tiep tuc thanh toan hoac huy truoc ngay nhan phong.';
        } elseif ($booking->status === 'confirmed') {
            $lines[] = 'Booking da xac nhan; owner chi duoc check-in tu ngay nhan phong tro di.';
        } elseif ($booking->status === 'staying') {
            $lines[] = 'Khach dang luu tru; hanh dong phu hop la checkout hoac doi phong neu can.';
        } elseif ($booking->status === 'completed') {
            $lines[] = $booking->review ? 'Booking da hoan tat va da co danh gia.' : 'Booking da hoan tat; customer co the danh gia neu chua danh gia.';
        } elseif ($booking->status === 'manual_review') {
            $lines[] = 'Booking can xu ly thu cong, thuong do thieu phong vat ly hoac thanh toan qua han can kiem tra.';
        }

        if ($booking->cancel_reason) {
            $lines[] = 'Ly do huy: ' . Str::limit($booking->cancel_reason, 180);
        }

        if ($isOperator) {
            $assignedRooms = $booking->roomAssignments
                ->map(fn ($assignment) => $assignment->room ? $assignment->room->room_number . ' (' . $assignment->room->status . ')' : null)
                ->filter()
                ->implode(', ');

            $lines[] = $assignedRooms ? "Phong da gan: {$assignedRooms}." : 'Chua co phong vat ly duoc gan.';
            if ($booking->financialTransaction) {
                $lines[] = "Giao dich noi bo: gross {$this->money($booking->financialTransaction->gross_amount)}, owner_amount {$this->money($booking->financialTransaction->owner_amount)}, trang thai {$booking->financialTransaction->status}.";
            }
        }

        if ($scope === 'checkout') {
            $lines[] = 'Trang checkout chi hien thi tong tien, trang thai giu phong va nut thanh toan VNPAY sandbox/demo; khong hien thi chia doanh thu noi bo.';
        }

        return $lines;
    }

    private function customerBookingHistoryLines(?User $viewer): array
    {
        if (!$viewer?->isCustomer()) {
            return ['Trang lich su booking chi danh cho customer dang dang nhap.'];
        }

        $counts = Booking::where('customer_id', $viewer->id)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($total, $status) => $this->bookingStatus((string) $status) . ": {$total}")
            ->implode(', ');

        return [
            $counts ? "Thong ke don cua customer: {$counts}." : 'Customer chua co booking.',
            'Trang nay dung de tiep tuc thanh toan, xem chi tiet, huy khi du dieu kien va danh gia stay da hoan tat.',
        ];
    }

    private function roomTypeLines(RoomType $roomType): array
    {
        $statusCounts = $roomType->rooms
            ->groupBy('status')
            ->map(fn ($rooms, $status) => "{$status}: " . $rooms->count())
            ->implode(', ');

        return [
            "Hang phong: {$roomType->name} tai {$roomType->hotel?->name}.",
            $roomType->description ? 'Mo ta: ' . Str::limit($roomType->description, 220) : null,
            "Gia: {$this->money($roomType->price_per_night)}/dem. Toi da {$roomType->max_guests} khach. Giuong {$roomType->bed_type}. Dien tich {$roomType->area}m2.",
            $roomType->amenities->isNotEmpty() ? 'Tien nghi hang phong: ' . $roomType->amenities->pluck('name')->implode(', ') . '.' : null,
            $statusCounts ? "Phong vat ly theo trang thai: {$statusCounts}." : 'Chua co phong vat ly cho hang phong nay.',
        ];
    }

    private function roomLines(Room $room): array
    {
        return [
            "Phong vat ly: {$room->room_number}.",
            $room->hotel ? "Khach san: {$room->hotel->name}." : null,
            $room->roomType ? "Hang phong: {$room->roomType->name}." : null,
            "Tang: {$room->floor}. Trang thai: {$room->status}.",
            $room->note ? 'Ghi chu: ' . Str::limit($room->note, 180) : null,
        ];
    }

    private function ownerRevenueLines(?User $viewer): array
    {
        if (!$viewer?->isOwner()) {
            return ['Trang doanh thu chi danh cho owner.'];
        }

        $transactions = FinancialTransaction::where('owner_id', $viewer->id);
        $pendingAdjustments = OwnerAdjustment::pending()
            ->where('owner_id', $viewer->id)
            ->sum('remaining_amount');

        return [
            'Trang doanh thu owner chi de xem doi soat, khong xu ly hoan tien truc tiep.',
            'Tong owner_amount lich su: ' . $this->money((clone $transactions)->sum('owner_amount')) . '.',
            'Dang cho doi soat: ' . $this->money((clone $transactions)->where('status', 'waiting_settlement')->sum('owner_amount')) . '.',
            'Da settled: ' . $this->money((clone $transactions)->where('status', 'settled')->sum('owner_amount')) . '.',
            'Khoan dieu chinh/khau tru dang cho tru vao settlement tiep theo: ' . $this->money($pendingAdjustments) . '.',
        ];
    }

    private function refundLines(Payment $payment): array
    {
        $payment->load(['booking.customer', 'booking.hotel.owner', 'booking.financialTransaction']);
        $booking = $payment->booking;
        $transaction = $booking?->financialTransaction;
        $settled = $transaction?->status === 'settled';

        return [
            "Refund payment #{$payment->id}: trang thai {$payment->status}, method {$payment->method}, amount {$this->money($payment->amount)}.",
            $booking ? "Booking {$booking->booking_code}, customer {$booking->contact_name}, khach san {$booking->hotel?->name}." : null,
            $booking?->hotel?->owner ? "Owner: {$booking->hotel->owner->name} - {$booking->hotel->owner->email}." : null,
            $booking?->cancel_reason ? 'Ly do customer huy: ' . Str::limit($booking->cancel_reason, 220) : null,
            $booking?->hotel?->cancellation_policy ? 'Chinh sach huy khach san: ' . Str::limit($booking->hotel->cancellation_policy, 260) : null,
            $transaction ? "FinancialTransaction: status {$transaction->status}, gross {$this->money($transaction->gross_amount)}, owner_amount {$this->money($transaction->owner_amount)}." : 'Chua co FinancialTransaction lien quan.',
            $settled ? 'Canh bao: booking da settled cho Owner; neu Admin xac nhan refund thi tao OwnerAdjustment/clawback va tru vao settlement tiep theo.' : 'Neu refund khi transaction chua settled thi danh dau payment refunded va transaction adjusted, khong tao OwnerAdjustment.',
        ];
    }

    private function settlementLines(FinancialTransaction $transaction): array
    {
        $transaction->load(['booking.hotel', 'owner', 'settlement.appliedAdjustments']);
        $pendingAdjustments = OwnerAdjustment::pending()
            ->where('owner_id', $transaction->owner_id)
            ->sum('remaining_amount');
        $actualTransfer = max(0, (float) $transaction->owner_amount - (float) $pendingAdjustments);

        return [
            "Settlement cho transaction #{$transaction->id}: status {$transaction->status}.",
            $transaction->booking ? "Booking {$transaction->booking->booking_code}, khach san {$transaction->booking->hotel?->name}." : null,
            $transaction->owner ? "Owner: {$transaction->owner->name} - {$transaction->owner->email}." : null,
            "Gross owner amount lich su: {$this->money($transaction->owner_amount)}.",
            "Pending deductions cua owner: {$this->money($pendingAdjustments)}.",
            "So tien du kien chuyen thuc te sau khau tru: {$this->money($actualTransfer)}.",
            $transaction->settlement ? "Settlement da tao: status {$transaction->settlement->status}, amount {$this->money($transaction->settlement->amount)}." : 'Chua co settlement duoc xac nhan.',
        ];
    }

    private function userLines(User $user): array
    {
        return [
            "User: {$user->name}, {$user->email}.",
            "Role: {$user->role}. Status: {$user->status}. Phone: " . ($user->phone ?: 'chua cap nhat') . '.',
            'So khach san owner: ' . $user->hotels()->count() . '. So booking customer: ' . $user->bookings()->count() . '.',
            $user->locked_reason ? 'Ly do khoa: ' . Str::limit($user->locked_reason, 180) : null,
        ];
    }

    private function partnerRequestLines(PartnerRequest $request): array
    {
        return [
            "Yeu cau doi tac #{$request->id}: {$request->business_name}, status {$request->status}.",
            $request->user ? "Customer gui: {$request->user->name} - {$request->user->email}." : null,
            "Lien he doi tac: {$request->contact_phone}, {$request->contact_email}.",
            'Dia chi kinh doanh: ' . $request->address . '.',
            $request->description ? 'Mo ta: ' . Str::limit($request->description, 240) : null,
            $request->reject_reason ? 'Ly do tu choi: ' . Str::limit($request->reject_reason, 180) : null,
        ];
    }

    private function canViewHotel(?Hotel $hotel, ?User $viewer): bool
    {
        if (!$hotel) {
            return false;
        }

        if ($hotel->status === 'active') {
            return true;
        }

        return (bool) ($viewer && ($viewer->isAdmin() || ($viewer->isOwner() && (int) $hotel->owner_id === (int) $viewer->id)));
    }

    private function canViewBooking(Booking $booking, ?User $viewer): bool
    {
        if (!$viewer) {
            return false;
        }

        if ($viewer->isAdmin()) {
            return true;
        }

        if ($viewer->isCustomer() && (int) $booking->customer_id === (int) $viewer->id) {
            return true;
        }

        $booking->loadMissing('hotel');

        return $viewer->isOwner() && (int) $booking->hotel?->owner_id === (int) $viewer->id;
    }

    private function bookingStatus(string $status): string
    {
        return [
            'pending_payment' => 'Cho thanh toan',
            'payment_expired' => 'Het han thanh toan',
            'payment_failed' => 'Thanh toan that bai',
            'confirmed' => 'Da xac nhan',
            'staying' => 'Dang luu tru',
            'completed' => 'Hoan tat',
            'cancelled' => 'Da huy',
            'no_show' => 'No-show',
            'manual_review' => 'Can xu ly thu cong',
        ][$status] ?? $status;
    }

    private function paymentStatus(string $status): string
    {
        return [
            'pending' => 'Cho thanh toan',
            'paid' => 'Da thanh toan',
            'failed' => 'Thanh toan that bai',
            'refunding' => 'Dang cho xu ly hoan tien',
            'refunded' => 'Da hoan tien',
            'non_refundable' => 'Khong hoan tien',
        ][$status] ?? $status;
    }

    private function money($amount): string
    {
        return number_format((float) $amount, 0, ',', '.') . ' VND';
    }

    private function date($value): string
    {
        return $value ? $value->format('d/m/Y') : 'chua ro';
    }

    private function time($value): string
    {
        return $value ? $value->format('H:i') : 'dang cap nhat';
    }
}
