<?php

use App\Http\Controllers\Customer\BookingController;
use App\Http\Controllers\Customer\BookingHistoryController;
use App\Http\Controllers\Customer\HomeController;
use App\Http\Controllers\Customer\HotelDetailController;
use App\Http\Controllers\Customer\HotelSearchController;
use App\Http\Controllers\Customer\PartnerRequestController;
use App\Http\Controllers\Customer\PaymentController;
use App\Http\Controllers\Customer\ReviewController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Customer Pages
|--------------------------------------------------------------------------
| Guest, Customer, Owner, Admin đều được xem các trang public.
*/

Route::get('/home', [HomeController::class, 'index'])
    ->name('customer.home');

Route::get('/hotels', [HotelSearchController::class, 'index'])
    ->name('customer.hotels.index');

Route::get('/hotels/{hotel}', [HotelDetailController::class, 'show'])
    ->name('customer.hotels.show');

/*
|--------------------------------------------------------------------------
| VNPAY Return / IPN
|--------------------------------------------------------------------------
| Return/IPN không đặt trong role:customer để VNPAY có thể gọi về.
*/

Route::get('/payments/vnpay/return', [PaymentController::class, 'vnpayReturn'])
    ->name('customer.payments.vnpay-return');

Route::get('/payments/vnpay/ipn', [PaymentController::class, 'vnpayIpn'])
    ->name('customer.payments.vnpay-ipn');

/*
|--------------------------------------------------------------------------
| Customer-only Actions
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'active', 'role:customer'])->group(function () {
    Route::get('/bookings/create', [BookingController::class, 'create'])
        ->name('customer.bookings.create');

    Route::post('/bookings', [BookingController::class, 'store'])
        ->name('customer.bookings.store');

    Route::get('/my/bookings', [BookingHistoryController::class, 'index'])
        ->name('customer.bookings.history');

    Route::get('/my/bookings/{booking}', [BookingHistoryController::class, 'show'])
        ->name('customer.bookings.show');

    Route::post('/my/bookings/{booking}/cancel', [BookingHistoryController::class, 'cancel'])
        ->name('customer.bookings.cancel');

    Route::get('/payments/{booking}/checkout', [PaymentController::class, 'checkout'])
        ->name('customer.payments.checkout');

    Route::post('/payments/{booking}/simulate-success', [PaymentController::class, 'simulateSuccess'])
        ->name('customer.payments.simulate-success');

    Route::post('/payments/{booking}/vnpay', [PaymentController::class, 'vnpay'])
        ->name('customer.payments.vnpay');

    Route::get('/bookings/{booking}/reviews/create', [ReviewController::class, 'create'])
        ->name('customer.reviews.create');

    Route::post('/bookings/{booking}/reviews', [ReviewController::class, 'store'])
        ->name('customer.reviews.store');

    Route::get('/partner-request/create', [PartnerRequestController::class, 'create'])
        ->name('customer.partner-request.create');

    Route::post('/partner-request', [PartnerRequestController::class, 'store'])
        ->name('customer.partner-request.store');
});