<?php

namespace App\Services\Booking;

use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomType;
use Carbon\Carbon;

class AvailabilityService
{
    public function availableRoomCount(RoomType $roomType, string $checkinDate, string $checkoutDate): int
    {
        $checkin = Carbon::parse($checkinDate)->toDateString();
        $checkout = Carbon::parse($checkoutDate)->toDateString();

        /*
         * Tổng phòng có thể bán theo đặc tả R2.3/R4.1:
         * - Không tính phòng bảo trì/tạm khóa.
         * - Không chỉ đếm status = available, vì phòng occupied/staying sẽ được trừ bằng booking chồng ngày.
         * - Các trạng thái cleaning/occupied vẫn là phòng vật lý có thể bán cho khoảng ngày khác nếu không bị booking chồng ngày.
         */
        $totalBookablePhysicalRooms = Room::where('room_type_id', $roomType->id)
            ->whereNotIn('status', ['maintenance', 'locked'])
            ->count();

        $reservedQuantity = Booking::query()
            ->whereHas('roomTypes', function ($query) use ($roomType) {
                $query->where('room_type_id', $roomType->id);
            })
            ->where(function ($query) {
                $query->whereIn('status', ['confirmed', 'staying'])
                    ->orWhere(function ($pendingQuery) {
                        $pendingQuery->where('status', 'pending_payment')
                            ->whereNotNull('hold_expires_at')
                            ->where('hold_expires_at', '>', now());
                    });
            })
            ->where(function ($query) use ($checkin, $checkout) {
                $query->where('checkin_date', '<', $checkout)
                    ->where('checkout_date', '>', $checkin);
            })
            ->with('roomTypes')
            ->get()
            ->sum(function ($booking) use ($roomType) {
                return $booking->roomTypes
                    ->where('room_type_id', $roomType->id)
                    ->sum('quantity');
            });

        return max(0, $totalBookablePhysicalRooms - $reservedQuantity);
    }

    public function isAvailable(RoomType $roomType, string $checkinDate, string $checkoutDate, int $quantity = 1): bool
    {
        return $this->availableRoomCount($roomType, $checkinDate, $checkoutDate) >= $quantity;
    }
}
