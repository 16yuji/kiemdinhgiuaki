<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Booking;
use App\Models\BookingRoomType;
use App\Models\Hotel;
use App\Models\Payment;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

$customer = User::where('email', 'customer.mai@travelmate.test')->firstOrFail();
$hotel = Hotel::where('status', 'active')->orderByDesc('id')->firstOrFail();
$roomType = RoomType::where('hotel_id', $hotel->id)->orderBy('price_per_night')->firstOrFail();

$booking = DB::transaction(function () use ($customer, $hotel, $roomType) {
    $booking = Booking::firstOrNew(['booking_code' => 'TMREPORT-CHECKOUT']);
    $booking->fill([
        'customer_id' => $customer->id,
        'hotel_id' => $hotel->id,
        'checkin_date' => Carbon::today()->addDays(5)->toDateString(),
        'checkout_date' => Carbon::today()->addDays(6)->toDateString(),
        'guest_count' => 2,
        'contact_name' => $customer->name,
        'contact_phone' => $customer->phone ?? '0900000000',
        'contact_email' => $customer->email,
        'special_request' => 'Booking dùng để chụp giao diện báo cáo.',
        'total_amount' => $roomType->price_per_night,
        'status' => 'pending_payment',
        'hold_expires_at' => Carbon::now()->addMinutes(15),
    ]);
    $booking->save();

    BookingRoomType::updateOrCreate(
        [
            'booking_id' => $booking->id,
            'room_type_id' => $roomType->id,
        ],
        [
            'quantity' => 1,
            'price_per_night' => $roomType->price_per_night,
            'subtotal' => $roomType->price_per_night,
        ]
    );

    Payment::updateOrCreate(
        ['booking_id' => $booking->id],
        [
            'amount' => $booking->total_amount,
            'method' => 'vnpay',
            'status' => 'pending',
        ]
    );

    return $booking;
});

echo $booking->id;

