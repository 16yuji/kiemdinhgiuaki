<?php

namespace App\Http\Middleware;

use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomType;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwnerOwnsResource
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->role !== 'owner') {
            abort(403, 'Bạn không có quyền truy cập chức năng này.');
        }

        /*
         * Middleware này kiểm tra quyền sở hữu cơ bản cho các tham số route:
         * - hotel
         * - room_type
         * - room
         * - booking
         *
         * Khi chưa có tham số nào, cho đi tiếp.
         */

        if ($request->route('hotel')) {
            $hotel = $this->resolveModel($request->route('hotel'), Hotel::class);

            if ($hotel && (int) $hotel->owner_id !== (int) $user->id) {
                abort(403, 'Bạn không có quyền thao tác trên khách sạn này.');
            }
        }

        if ($request->route('room_type')) {
            $roomType = $this->resolveModel($request->route('room_type'), RoomType::class);

            if ($roomType && (int) $roomType->hotel->owner_id !== (int) $user->id) {
                abort(403, 'Bạn không có quyền thao tác trên hạng phòng này.');
            }
        }

        if ($request->route('room')) {
            $room = $this->resolveModel($request->route('room'), Room::class);

            if ($room && (int) $room->hotel->owner_id !== (int) $user->id) {
                abort(403, 'Bạn không có quyền thao tác trên phòng này.');
            }
        }

        if ($request->route('booking')) {
            $booking = $this->resolveModel($request->route('booking'), Booking::class);

            if ($booking && (int) $booking->hotel->owner_id !== (int) $user->id) {
                abort(403, 'Bạn không có quyền thao tác trên đơn đặt phòng này.');
            }
        }

        return $next($request);
    }

    private function resolveModel(mixed $value, string $class)
    {
        if ($value instanceof $class) {
            return $value;
        }

        if (is_numeric($value)) {
            return $class::find($value);
        }

        return null;
    }
}