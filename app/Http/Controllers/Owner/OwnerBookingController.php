<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Room;
use App\Services\SystemLogService;
use Illuminate\Http\Request;

class OwnerBookingController extends Controller
{
    public function index(Request $request)
    {
        $hotelIds = Hotel::where('owner_id', auth()->id())->pluck('id');

        $bookings = Booking::with(['customer', 'hotel', 'payment', 'roomTypes.roomType'])
            ->whereIn('hotel_id', $hotelIds)
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->keyword, function ($query, $keyword) {
                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->where('booking_code', 'like', "%{$keyword}%")
                        ->orWhere('contact_name', 'like', "%{$keyword}%")
                        ->orWhere('contact_phone', 'like', "%{$keyword}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('owner.bookings.index', compact('bookings'));
    }

    public function show(Booking $booking)
    {
        $this->authorizeBooking($booking);

        $booking->load([
            'customer',
            'hotel',
            'roomTypes.roomType',
            'roomAssignments.room.roomType',
            'payment',
            'financialTransaction',
            'review',
        ]);

        return view('owner.bookings.show', compact('booking'));
    }

    public function resolveManualReview(Request $request, Booking $booking)
    {
        $this->authorizeBooking($booking);

        if ($booking->status !== 'manual_review') {
            return redirect()
                ->route('owner.bookings.show', $booking)
                ->with('error', 'Chỉ đơn ở trạng thái Cần xử lý thủ công mới được mở lại.');
        }

        $data = $request->validate([
            'manual_review_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $booking->load(['roomTypes.roomType']);

        foreach ($booking->roomTypes as $bookingRoomType) {
            $availableCount = Room::where('hotel_id', $booking->hotel_id)
                ->where('room_type_id', $bookingRoomType->room_type_id)
                ->where('status', 'available')
                ->count();

            if ($availableCount < (int) $bookingRoomType->quantity) {
                return redirect()
                    ->route('owner.bookings.show', $booking)
                    ->with('error', 'Vẫn chưa đủ phòng sẵn sàng thuộc hạng phòng khách đã đặt. Vui lòng cập nhật trạng thái phòng trước.');
            }
        }

        $note = $data['manual_review_note'] ?? 'Owner đã kiểm tra và sắp xếp đủ phòng sẵn sàng để check-in.';

        $booking->update([
            'status' => 'confirmed',
            'manual_review_reason' => null,
            'checkin_note' => trim(($booking->checkin_note ? $booking->checkin_note . "\n" : '') . 'Xử lý thủ công: ' . $note),
        ]);

        SystemLogService::write(
            'booking_resolve_manual_review',
            'bookings',
            Booking::class,
            $booking->id,
            'Owner xử lý đơn cần xử lý thủ công và đưa đơn về trạng thái đã xác nhận.',
            [
                'booking_code' => $booking->booking_code,
                'note' => $note,
            ]
        );

        return redirect()
            ->route('owner.bookings.show', $booking)
            ->with('success', 'Đã xử lý thủ công. Đơn được đưa về trạng thái Đã xác nhận, bây giờ có thể Check-in lại.');
    }

    private function authorizeBooking(Booking $booking): void
    {
        if ((int) $booking->hotel->owner_id !== (int) auth()->id()) {
            abort(403, 'Bạn không có quyền xem đơn đặt phòng này.');
        }
    }
}