<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingRoomAssignment;
use App\Models\Room;
use App\Services\SystemLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckInController extends Controller
{
    public function create(Booking $booking)
    {
        $this->authorizeBooking($booking);

        if ($booking->status !== 'confirmed') {
            return redirect()
                ->route('owner.bookings.show', $booking)
                ->with('error', 'Chỉ đơn đã xác nhận mới được check-in.');
        }

        if ($booking->checkin_date->isFuture()) {
            return redirect()
                ->route('owner.bookings.show', $booking)
                ->with('error', 'Chưa đến ngày nhận phòng nên không thể check-in.');
        }

        $booking->load(['hotel', 'roomTypes.roomType']);

        $requiredQuantity = $booking->roomTypes->sum('quantity');
        $roomTypeIds = $booking->roomTypes->pluck('room_type_id');
        $insufficientRoomTypes = [];

        foreach ($booking->roomTypes as $bookingRoomType) {
            $availableForType = Room::where('hotel_id', $booking->hotel_id)
                ->where('room_type_id', $bookingRoomType->room_type_id)
                ->where('status', 'available')
                ->count();

            if ($availableForType < (int) $bookingRoomType->quantity) {
                $insufficientRoomTypes[] = $bookingRoomType->roomType->name ?? ('#' . $bookingRoomType->room_type_id);
            }
        }

        $rooms = Room::with('roomType')
            ->where('hotel_id', $booking->hotel_id)
            ->whereIn('room_type_id', $roomTypeIds)
            ->where('status', 'available')
            ->orderBy('room_number')
            ->get();

        if ($rooms->count() < $requiredQuantity || !empty($insufficientRoomTypes)) {
            $booking->update([
                'status' => 'manual_review',
                'manual_review_reason' => 'Không đủ phòng sẵn sàng để check-in. Cần Owner/Admin xử lý thủ công.',
            ]);

            SystemLogService::write(
                'booking_manual_review_checkin',
                'bookings',
                Booking::class,
                $booking->id,
                'Đơn được chuyển sang cần xử lý thủ công do không đủ phòng sẵn sàng khi check-in.',
                [
                    'booking_code' => $booking->booking_code,
                    'required_quantity' => $requiredQuantity,
                    'available_room_count' => $rooms->count(),
                ]
            );

            return redirect()
                ->route('owner.bookings.show', $booking)
                ->with('error', 'Không đủ phòng sẵn sàng. Đơn đã chuyển sang trạng thái cần xử lý thủ công.');
        }

        return view('owner.bookings.check-in', compact('booking', 'rooms'));
    }

    public function store(Request $request, Booking $booking)
    {
        $this->authorizeBooking($booking);

        if ($booking->status !== 'confirmed') {
            return redirect()
                ->route('owner.bookings.show', $booking)
                ->with('error', 'Chỉ đơn đã xác nhận mới được check-in.');
        }

        if ($booking->checkin_date->isFuture()) {
            return redirect()
                ->route('owner.bookings.show', $booking)
                ->with('error', 'Chưa đến ngày nhận phòng nên không thể check-in.');
        }

        $data = $request->validate([
            'room_ids' => ['required', 'array'],
            'room_ids.*' => ['exists:rooms,id'],
            'checkin_note' => ['nullable', 'string', 'max:1000'],
        ], [
            'room_ids.required' => 'Vui lòng chọn phòng cụ thể cho khách.',
        ]);

        $booking->load(['roomTypes.roomType']);

        $requiredQuantity = $booking->roomTypes->sum('quantity');

        if (count($data['room_ids']) !== $requiredQuantity) {
            return back()
                ->withInput()
                ->with('error', "Bạn cần chọn đúng {$requiredQuantity} phòng.");
        }

        $rooms = Room::whereIn('id', $data['room_ids'])
            ->where('hotel_id', $booking->hotel_id)
            ->where('status', 'available')
            ->get();

        if ($rooms->count() !== $requiredQuantity) {
            $booking->update([
                'status' => 'manual_review',
                'manual_review_reason' => 'Một số phòng được chọn không còn sẵn sàng khi check-in. Cần xử lý thủ công.',
            ]);

            SystemLogService::write(
                'booking_manual_review_checkin_room_unavailable',
                'bookings',
                Booking::class,
                $booking->id,
                'Đơn được chuyển sang cần xử lý thủ công do phòng được chọn không còn sẵn sàng.',
                [
                    'booking_code' => $booking->booking_code,
                    'selected_room_ids' => $data['room_ids'],
                ]
            );

            return redirect()
                ->route('owner.bookings.show', $booking)
                ->with('error', 'Một số phòng không hợp lệ hoặc không còn sẵn sàng. Đơn đã chuyển sang cần xử lý thủ công.');
        }

        foreach ($booking->roomTypes as $bookingRoomType) {
            $selectedCount = $rooms
                ->where('room_type_id', $bookingRoomType->room_type_id)
                ->count();

            if ($selectedCount !== (int) $bookingRoomType->quantity) {
                return back()
                    ->withInput()
                    ->with('error', 'Số phòng được chọn không khớp với từng hạng phòng khách đã đặt.');
            }
        }

        try {
            DB::transaction(function () use ($booking, $data, $requiredQuantity) {
            $lockedBooking = Booking::whereKey($booking->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedBooking->status !== 'confirmed') {
                throw new \RuntimeException('Đơn đã được xử lý ở trạng thái khác. Vui lòng tải lại trang.');
            }

            $rooms = Room::whereIn('id', $data['room_ids'])
                ->where('hotel_id', $booking->hotel_id)
                ->where('status', 'available')
                ->lockForUpdate()
                ->get();

            if ($rooms->count() !== $requiredQuantity) {
                $booking->update([
                    'status' => 'manual_review',
                    'manual_review_reason' => 'Một số phòng được chọn không còn sẵn sàng khi check-in. Cần xử lý thủ công.',
                ]);

                SystemLogService::write(
                    'booking_manual_review_checkin_room_unavailable',
                    'bookings',
                    Booking::class,
                    $booking->id,
                    'Đơn được chuyển sang cần xử lý thủ công do phòng được chọn không còn sẵn sàng.',
                    [
                        'booking_code' => $booking->booking_code,
                        'selected_room_ids' => $data['room_ids'],
                    ]
                );

                throw new \RuntimeException('Một số phòng không hợp lệ hoặc không còn sẵn sàng. Đơn đã chuyển sang cần xử lý thủ công.');
            }

            foreach ($booking->roomTypes as $bookingRoomType) {
                $selectedCount = $rooms
                    ->where('room_type_id', $bookingRoomType->room_type_id)
                    ->count();

                if ($selectedCount !== (int) $bookingRoomType->quantity) {
                    throw new \RuntimeException('Số phòng được chọn không khớp với từng hạng phòng khách đã đặt.');
                }
            }

            foreach ($rooms as $room) {
                BookingRoomAssignment::create([
                    'booking_id' => $booking->id,
                    'room_id' => $room->id,
                    'assigned_at' => now(),
                ]);

                $room->update([
                    'status' => 'occupied',
                ]);
            }

            $booking->update([
                'status' => 'staying',
                'checked_in_at' => now(),
                'checkin_note' => $data['checkin_note'] ?? null,
                'manual_review_reason' => null,
            ]);

            SystemLogService::write(
                'booking_checkin',
                'bookings',
                Booking::class,
                $booking->id,
                'Owner xác nhận check-in và gán phòng cho khách.',
                [
                    'booking_code' => $booking->booking_code,
                    'room_ids' => $rooms->pluck('id')->toArray(),
                    'checkin_note' => $data['checkin_note'] ?? null,
                ]
            );
            });
        } catch (\RuntimeException $exception) {
            return redirect()
                ->route('owner.bookings.show', $booking)
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('owner.bookings.show', $booking)
            ->with('success', 'Check-in thành công. Đơn đã chuyển sang trạng thái Đang lưu trú.');
    }

    private function authorizeBooking(Booking $booking): void
    {
        if ((int) $booking->hotel->owner_id !== (int) auth()->id()) {
            abort(403, 'Bạn không có quyền thao tác trên đơn này.');
        }
    }
}
