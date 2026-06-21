<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingRoomType;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomType;
use App\Services\Booking\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function create(Request $request, AvailabilityService $availabilityService)
    {
        $data = $request->validate([
            'hotel_id' => ['required', 'exists:hotels,id'],
            'room_type_id' => ['required', 'exists:room_types,id'],
            'checkin_date' => ['required', 'date', 'after_or_equal:today'],
            'checkout_date' => ['required', 'date', 'after:checkin_date'],
            'guests' => ['required', 'integer', 'min:1'],
        ]);

        $hotel = Hotel::where('status', 'active')->findOrFail($data['hotel_id']);

        $roomType = RoomType::where('status', 'active')
            ->where('hotel_id', $hotel->id)
            ->findOrFail($data['room_type_id']);

        $availableCount = $availabilityService->availableRoomCount(
            $roomType,
            $data['checkin_date'],
            $data['checkout_date']
        );

        if ($availableCount < 1) {
            return redirect()
                ->route('customer.hotels.show', [
                    'hotel' => $hotel,
                    'checkin_date' => $data['checkin_date'],
                    'checkout_date' => $data['checkout_date'],
                    'guests' => $data['guests'],
                ])
                ->with('error', 'Hạng phòng này hiện không còn phòng trống.');
        }

        $nights = Carbon::parse($data['checkin_date'])
            ->diffInDays(Carbon::parse($data['checkout_date']));

        return view('customer.bookings.create', [
            'hotel' => $hotel,
            'roomType' => $roomType,
            'checkinDate' => $data['checkin_date'],
            'checkoutDate' => $data['checkout_date'],
            'guests' => (int) $data['guests'],
            'availableCount' => $availableCount,
            'nights' => $nights,
        ]);
    }

    public function store(Request $request, AvailabilityService $availabilityService)
    {
        $data = $request->validate([
            'hotel_id' => ['required', 'exists:hotels,id'],
            'room_type_id' => ['required', 'exists:room_types,id'],
            'checkin_date' => ['required', 'date', 'after_or_equal:today'],
            'checkout_date' => ['required', 'date', 'after:checkin_date'],
            'guest_count' => ['required', 'integer', 'min:1'],
            'quantity' => ['required', 'integer', 'min:1'],
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:30'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'special_request' => ['nullable', 'string'],
        ], [
            'quantity.required' => 'Vui lòng nhập số lượng phòng.',
            'contact_name.required' => 'Vui lòng nhập họ tên người liên hệ.',
            'contact_phone.required' => 'Vui lòng nhập số điện thoại liên hệ.',
        ]);

        $hotel = Hotel::where('status', 'active')->findOrFail($data['hotel_id']);

        $roomType = RoomType::where('status', 'active')
            ->where('hotel_id', $hotel->id)
            ->findOrFail($data['room_type_id']);

        if ($data['guest_count'] > $roomType->max_guests * $data['quantity']) {
            return back()
                ->withInput()
                ->with('error', 'Số khách vượt quá sức chứa của số phòng đã chọn.');
        }

        $availableCount = $availabilityService->availableRoomCount(
            $roomType,
            $data['checkin_date'],
            $data['checkout_date']
        );

        if ($availableCount < $data['quantity']) {
            return back()
                ->withInput()
                ->with('error', 'Số lượng phòng còn trống không đủ.');
        }

        try {
            $booking = DB::transaction(function () use ($data, $hotel, $roomType, $availabilityService) {
                Room::where('room_type_id', $roomType->id)
                    ->whereNotIn('status', ['maintenance', 'locked'])
                    ->lockForUpdate()
                    ->get(['id']);

                $availableCount = $availabilityService->availableRoomCount(
                    $roomType,
                    $data['checkin_date'],
                    $data['checkout_date']
                );

                if ($availableCount < $data['quantity']) {
                    throw new \RuntimeException('Số lượng phòng còn trống không đủ.');
                }

            $nights = Carbon::parse($data['checkin_date'])
                ->diffInDays(Carbon::parse($data['checkout_date']));

            $subtotal = $roomType->price_per_night * $data['quantity'] * $nights;

            $booking = Booking::create([
                'customer_id' => auth()->id(),
                'hotel_id' => $hotel->id,
                'booking_code' => 'BK' . now()->format('YmdHis') . strtoupper(Str::random(4)),
                'checkin_date' => $data['checkin_date'],
                'checkout_date' => $data['checkout_date'],
                'guest_count' => $data['guest_count'],
                'contact_name' => $data['contact_name'],
                'contact_phone' => $data['contact_phone'],
                'contact_email' => $data['contact_email'] ?? auth()->user()->email,
                'special_request' => $data['special_request'] ?? null,
                'total_amount' => $subtotal,
                'status' => 'pending_payment',
                'hold_expires_at' => now()->addMinutes(15),
            ]);

            BookingRoomType::create([
                'booking_id' => $booking->id,
                'room_type_id' => $roomType->id,
                'quantity' => $data['quantity'],
                'price_per_night' => $roomType->price_per_night,
                'nights' => $nights,
                'subtotal' => $subtotal,
            ]);

            $booking->payment()->create([
                'amount' => $subtotal,
                'method' => 'fake',
                'status' => 'pending',
            ]);

            return $booking;
            });
        } catch (\RuntimeException $exception) {
            return back()
                ->withInput()
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('customer.payments.checkout', $booking)
            ->with('success', 'Tạo đơn đặt phòng thành công. Vui lòng thanh toán trong 15 phút.');
    }
}
