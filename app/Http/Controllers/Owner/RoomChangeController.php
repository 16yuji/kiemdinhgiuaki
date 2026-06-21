<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingRoomAssignment;
use App\Models\Room;
use App\Services\SystemLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomChangeController extends Controller
{
    public function create(Booking $booking)
    {
        $this->authorizeBooking($booking);

        if ($booking->status !== 'staying') {
            return redirect()
                ->route('owner.bookings.show', $booking)
                ->with('error', 'Chỉ đơn đang lưu trú mới được đổi phòng.');
        }

        $booking->load([
            'hotel',
            'roomAssignments.room.roomType',
        ]);

        $currentAssignments = $booking->roomAssignments
            ->whereNull('released_at')
            ->filter(fn ($assignment) => $assignment->room !== null)
            ->values();

        if ($currentAssignments->isEmpty()) {
            return redirect()
                ->route('owner.bookings.show', $booking)
                ->with('error', 'Đơn này chưa có phòng đang sử dụng để đổi.');
        }

        $currentRoomTypeIds = $currentAssignments
            ->pluck('room.room_type_id')
            ->filter()
            ->unique()
            ->values();

        $availableRooms = Room::with('roomType')
            ->where('hotel_id', $booking->hotel_id)
            ->whereIn('room_type_id', $currentRoomTypeIds)
            ->where('status', 'available')
            ->orderBy('room_type_id')
            ->orderBy('room_number')
            ->get();

        return view('owner.bookings.change-room', compact(
            'booking',
            'currentAssignments',
            'availableRooms'
        ));
    }

    public function store(Request $request, Booking $booking)
    {
        $this->authorizeBooking($booking);

        if ($booking->status !== 'staying') {
            return redirect()
                ->route('owner.bookings.show', $booking)
                ->with('error', 'Chỉ đơn đang lưu trú mới được đổi phòng.');
        }

        $data = $request->validate([
            'assignment_id' => ['required', 'exists:booking_room_assignments,id'],
            'new_room_id' => ['required', 'exists:rooms,id'],
            'old_room_next_status' => ['required', 'in:cleaning,maintenance'],
            'change_reason' => ['required', 'string', 'max:1000'],
        ], [
            'assignment_id.required' => 'Vui lòng chọn phòng hiện tại cần đổi.',
            'new_room_id.required' => 'Vui lòng chọn phòng mới.',
            'old_room_next_status.required' => 'Vui lòng chọn trạng thái tiếp theo của phòng cũ.',
            'old_room_next_status.in' => 'Trạng thái phòng cũ không hợp lệ.',
            'change_reason.required' => 'Vui lòng nhập lý do đổi phòng.',
        ]);

        $assignment = BookingRoomAssignment::with('room.roomType')
            ->where('id', $data['assignment_id'])
            ->where('booking_id', $booking->id)
            ->whereNull('released_at')
            ->first();

        if (!$assignment || !$assignment->room) {
            return back()
                ->withInput()
                ->with('error', 'Phòng hiện tại không hợp lệ hoặc đã được trả.');
        }

        $oldRoom = $assignment->room;

        $newRoom = Room::with('roomType')
            ->where('id', $data['new_room_id'])
            ->where('hotel_id', $booking->hotel_id)
            ->where('status', 'available')
            ->first();

        if (!$newRoom) {
            return back()
                ->withInput()
                ->with('error', 'Phòng mới không hợp lệ hoặc không còn sẵn sàng.');
        }

        if ((int) $newRoom->room_type_id !== (int) $oldRoom->room_type_id) {
            return back()
                ->withInput()
                ->with('error', 'Phòng mới phải cùng hạng phòng với phòng hiện tại theo đặc tả đổi phòng.');
        }

        try {
            DB::transaction(function () use ($booking, $assignment, $oldRoom, $newRoom, $data) {
            $assignment = BookingRoomAssignment::whereKey($assignment->id)
                ->where('booking_id', $booking->id)
                ->whereNull('released_at')
                ->lockForUpdate()
                ->first();

            $oldRoom = Room::whereKey($oldRoom->id)
                ->where('hotel_id', $booking->hotel_id)
                ->lockForUpdate()
                ->first();

            $newRoom = Room::whereKey($newRoom->id)
                ->where('hotel_id', $booking->hotel_id)
                ->lockForUpdate()
                ->first();

            if (!$assignment || !$oldRoom || !$newRoom) {
                throw new \RuntimeException('Thông tin phòng đổi không còn hợp lệ. Vui lòng tải lại trang.');
            }

            if ($newRoom->status !== 'available') {
                throw new \RuntimeException('Phòng mới không còn sẵn sàng. Vui lòng chọn phòng khác.');
            }

            if ((int) $newRoom->room_type_id !== (int) $oldRoom->room_type_id) {
                throw new \RuntimeException('Phòng mới phải cùng hạng phòng với phòng hiện tại.');
            }

            $assignment->update([
                'released_at' => now(),
                'note' => trim(($assignment->note ? $assignment->note . "\n" : '') . 'Đổi phòng: ' . $data['change_reason']),
            ]);

            $oldRoom->update([
                'status' => $data['old_room_next_status'],
            ]);

            BookingRoomAssignment::create([
                'booking_id' => $booking->id,
                'room_id' => $newRoom->id,
                'assigned_at' => now(),
                'note' => 'Đổi từ phòng ' . $oldRoom->room_number . '. Lý do: ' . $data['change_reason'],
            ]);

            $newRoom->update([
                'status' => 'occupied',
            ]);

            SystemLogService::write(
                'booking_change_room',
                'bookings',
                Booking::class,
                $booking->id,
                'Owner đổi phòng cho khách đang lưu trú.',
                [
                    'booking_code' => $booking->booking_code,
                    'old_room_id' => $oldRoom->id,
                    'old_room_number' => $oldRoom->room_number,
                    'old_room_next_status' => $data['old_room_next_status'],
                    'new_room_id' => $newRoom->id,
                    'new_room_number' => $newRoom->room_number,
                    'reason' => $data['change_reason'],
                ]
            );
            });
        } catch (\RuntimeException $exception) {
            return back()
                ->withInput()
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('owner.bookings.show', $booking)
            ->with('success', 'Đổi phòng thành công. Phòng mới chuyển sang Đang sử dụng, phòng cũ đã được cập nhật trạng thái.');
    }

    private function authorizeBooking(Booking $booking): void
    {
        if ((int) $booking->hotel->owner_id !== (int) auth()->id()) {
            abort(403, 'Bạn không có quyền thao tác trên đơn này.');
        }
    }
}
