<?php

use App\Http\Controllers\Owner\CheckInController;
use App\Http\Controllers\Owner\CheckOutController;
use App\Http\Controllers\Owner\DashboardController;
use App\Http\Controllers\Owner\HotelController;
use App\Http\Controllers\Owner\NoShowController;
use App\Http\Controllers\Owner\OwnerBookingController;
use App\Http\Controllers\Owner\RoomController;
use App\Http\Controllers\Owner\RoomTypeController;
use App\Http\Controllers\Owner\ReviewReplyController;
use App\Http\Controllers\Owner\RevenueController;
use App\Http\Controllers\Owner\HotelStatusAppealController;
use App\Http\Controllers\Owner\HotelImageController;
use App\Http\Controllers\Owner\RoomChangeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active', 'role:owner'])
    ->prefix('owner')
    ->name('owner.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::resource('hotels', HotelController::class);
        Route::resource('room-types', RoomTypeController::class);
        Route::resource('rooms', RoomController::class);

        Route::get('/bookings', [OwnerBookingController::class, 'index'])
            ->name('bookings.index');

        Route::get('/bookings/{booking}', [OwnerBookingController::class, 'show'])
            ->name('bookings.show');

        Route::post('/bookings/{booking}/resolve-manual-review', [OwnerBookingController::class, 'resolveManualReview'])
            ->name('bookings.resolve-manual-review');

        Route::get('/bookings/{booking}/check-in', [CheckInController::class, 'create'])
            ->name('bookings.check-in.create');

        Route::post('/bookings/{booking}/check-in', [CheckInController::class, 'store'])
            ->name('bookings.check-in.store');

        Route::post('/bookings/{booking}/check-out', [CheckOutController::class, 'store'])
            ->name('bookings.check-out.store');

        Route::post('/bookings/{booking}/no-show', [NoShowController::class, 'store'])
            ->name('bookings.no-show.store');

        Route::get('/reviews', [ReviewReplyController::class, 'index'])
            ->name('reviews.index');

        Route::post('/reviews/{review}/reply', [ReviewReplyController::class, 'store'])
            ->name('reviews.reply.store');
        
        Route::get('/revenues', [RevenueController::class, 'index'])
            ->name('revenues.index');

        Route::get('/hotels/{hotel}/appeal', [HotelStatusAppealController::class, 'create'])
            ->name('hotels.appeal.create');

        Route::post('/hotels/{hotel}/appeal', [HotelStatusAppealController::class, 'store'])
            ->name('hotels.appeal.store');

        Route::delete('/hotel-images/{hotelImage}', [HotelImageController::class, 'destroy'])
            ->name('hotel-images.destroy');

        Route::get('/bookings/{booking}/change-room', [RoomChangeController::class, 'create'])
            ->name('bookings.change-room.create');

        Route::post('/bookings/{booking}/change-room', [RoomChangeController::class, 'store'])
            ->name('bookings.change-room.store');
});