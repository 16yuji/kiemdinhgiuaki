<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Booking;
use App\Models\FinancialTransaction;
use App\Models\Hotel;
use App\Models\HotelStatusAppeal;
use App\Models\PartnerRequest;
use App\Models\Payment;
use App\Models\Review;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;

$owner = User::where('email', 'owner@example.com')->first();
$customer = User::where('email', 'customer@example.com')->first();
$admin = User::where('email', 'admin@example.com')->first();

$hotel = Hotel::query()
    ->when($owner, fn ($q) => $q->where('owner_id', $owner->id))
    ->orderByDesc('id')
    ->first() ?: Hotel::query()->orderByDesc('id')->first();

$roomType = RoomType::query()
    ->when($hotel, fn ($q) => $q->where('hotel_id', $hotel->id))
    ->orderByDesc('id')
    ->first() ?: RoomType::query()->orderByDesc('id')->first();

$room = Room::query()
    ->when($roomType, fn ($q) => $q->where('room_type_id', $roomType->id))
    ->orderByDesc('id')
    ->first() ?: Room::query()->orderByDesc('id')->first();

$booking = Booking::query()
    ->when($customer, fn ($q) => $q->where('customer_id', $customer->id))
    ->orderByDesc('id')
    ->first() ?: Booking::query()->orderByDesc('id')->first();

$ownerBooking = Booking::query()
    ->when($owner, fn ($q) => $q->whereHas('hotel', fn ($h) => $h->where('owner_id', $owner->id)))
    ->orderByDesc('id')
    ->first() ?: $booking;

$payment = Payment::query()->whereIn('status', ['refunding', 'paid', 'refunded'])->orderByDesc('id')->first()
    ?: Payment::query()->orderByDesc('id')->first();

$financial = FinancialTransaction::query()->orderByDesc('id')->first();
$partnerRequest = PartnerRequest::query()->orderByDesc('id')->first();
$review = Review::query()->orderByDesc('id')->first();
$appeal = class_exists(HotelStatusAppeal::class)
    ? HotelStatusAppeal::query()->orderByDesc('id')->first()
    : null;

echo json_encode([
    'counts' => [
        'users' => User::count(),
        'hotels' => Hotel::count(),
        'roomTypes' => RoomType::count(),
        'rooms' => Room::count(),
        'bookings' => Booking::count(),
        'payments' => Payment::count(),
        'financials' => FinancialTransaction::count(),
        'partnerRequests' => PartnerRequest::count(),
        'reviews' => Review::count(),
        'appeals' => class_exists(HotelStatusAppeal::class) ? HotelStatusAppeal::count() : 0,
    ],
    'users' => [
        'admin' => $admin?->id,
        'owner' => $owner?->id,
        'customer' => $customer?->id,
    ],
    'userSamples' => User::query()
        ->orderBy('id')
        ->get(['id', 'name', 'email', 'role'])
        ->map(fn ($user) => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ]),
    'ids' => [
        'hotel' => $hotel?->id,
        'roomType' => $roomType?->id,
        'room' => $room?->id,
        'booking' => $booking?->id,
        'ownerBooking' => $ownerBooking?->id,
        'payment' => $payment?->id,
        'financial' => $financial?->id,
        'partnerRequest' => $partnerRequest?->id,
        'review' => $review?->id,
        'appeal' => $appeal?->id,
    ],
    'bookings' => Booking::with('payment')
        ->orderByDesc('id')
        ->take(20)
        ->get()
        ->map(fn ($booking) => [
            'id' => $booking->id,
            'code' => $booking->booking_code,
            'customer_id' => $booking->customer_id,
            'hotel_id' => $booking->hotel_id,
            'status' => $booking->status,
            'payment_status' => $booking->payment?->status,
            'checkin_date' => optional($booking->checkin_date)->toDateString(),
        ]),
    'payments' => Payment::with('booking')
        ->orderByDesc('id')
        ->take(20)
        ->get()
        ->map(fn ($payment) => [
            'id' => $payment->id,
            'booking_id' => $payment->booking_id,
            'booking_status' => $payment->booking?->status,
            'status' => $payment->status,
        ]),
    'hotels' => Hotel::with('owner')
        ->orderByDesc('id')
        ->get()
        ->map(fn ($hotel) => [
            'id' => $hotel->id,
            'name' => $hotel->name,
            'owner_id' => $hotel->owner_id,
            'owner_email' => $hotel->owner?->email,
            'status' => $hotel->status,
        ]),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
