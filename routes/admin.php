<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PartnerRequestController;
use App\Http\Controllers\Admin\ReviewModerationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SettlementController;
use App\Http\Controllers\Admin\HotelModerationController;
use App\Http\Controllers\Admin\HotelStatusAppealController; 
use App\Http\Controllers\Admin\RefundController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/users', [UserController::class, 'index'])
            ->name('users.index');

        Route::get('/users/{user}', [UserController::class, 'show'])
            ->name('users.show');

        Route::get('/users/{user}/lock', [UserController::class, 'confirmLock'])
            ->name('users.lock.confirm');

        Route::post('/users/{user}/lock', [UserController::class, 'lock'])
            ->name('users.lock');

        Route::post('/users/{user}/unlock', [UserController::class, 'unlock'])
            ->name('users.unlock');

        Route::get('/partner-requests', [PartnerRequestController::class, 'index'])
            ->name('partner-requests.index');

        Route::get('/partner-requests/{partnerRequest}', [PartnerRequestController::class, 'show'])
            ->name('partner-requests.show');

        Route::post('/partner-requests/{partnerRequest}/approve', [PartnerRequestController::class, 'approve'])
            ->name('partner-requests.approve');

        Route::post('/partner-requests/{partnerRequest}/reject', [PartnerRequestController::class, 'reject'])
            ->name('partner-requests.reject');

        Route::get('/reviews', [ReviewModerationController::class, 'index'])
            ->name('reviews.index');

        Route::post('/reviews/{review}/hide', [ReviewModerationController::class, 'hide'])
            ->name('reviews.hide');

        Route::post('/reviews/{review}/restore', [ReviewModerationController::class, 'restore'])
            ->name('reviews.restore');

        Route::get('/settlements', [SettlementController::class, 'index'])
            ->name('settlements.index');

        Route::get('/settlements/{financialTransaction}', [SettlementController::class, 'show'])
            ->name('settlements.show');

        Route::post('/settlements/{financialTransaction}/confirm', [SettlementController::class, 'confirm'])
            ->name('settlements.confirm');

        Route::get('/hotels', [HotelModerationController::class, 'index'])
            ->name('hotels.index');

        Route::get('/hotels/{hotel}', [HotelModerationController::class, 'show'])
            ->name('hotels.show');

        Route::get('/hotels/{hotel}/status', [HotelModerationController::class, 'statusForm'])
            ->name('hotels.status');

        Route::post('/hotels/{hotel}/status', [HotelModerationController::class, 'updateStatus'])
            ->name('hotels.status.update');

        Route::get('/hotel-appeals', [HotelStatusAppealController::class, 'index'])
            ->name('hotel-appeals.index');

        Route::get('/hotel-appeals/{appeal}', [HotelStatusAppealController::class, 'show'])
            ->name('hotel-appeals.show');

        Route::post('/hotel-appeals/{appeal}/approve', [HotelStatusAppealController::class, 'approve'])
            ->name('hotel-appeals.approve');

        Route::post('/hotel-appeals/{appeal}/reject', [HotelStatusAppealController::class, 'reject'])
            ->name('hotel-appeals.reject');
    
        Route::get('/refunds', [RefundController::class, 'index'])
            ->name('refunds.index');

        Route::get('/refunds/{payment}', [RefundController::class, 'show'])
            ->name('refunds.show');

        Route::post('/refunds/{payment}/refunded', [RefundController::class, 'markRefunded'])
            ->name('refunds.refunded');

        Route::post('/refunds/{payment}/non-refundable', [RefundController::class, 'markNonRefundable'])
            ->name('refunds.non-refundable');
});