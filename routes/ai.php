<?php

use App\Http\Controllers\Ai\AiChatController;
use App\Http\Controllers\Ai\ReviewSummaryController;
use App\Http\Controllers\Ai\RoomRecommendationController;
use Illuminate\Support\Facades\Route;

Route::prefix('ai')
    ->name('ai.')
    ->middleware(['throttle:30,1'])
    ->group(function () {
        Route::post('/chat', [AiChatController::class, 'store'])
            ->name('chat.store');

        Route::get('/room-types/{roomType}/recommendations', [RoomRecommendationController::class, 'index'])
            ->name('room-recommendations.index');
    });

Route::middleware(['auth', 'active', 'throttle:12,1'])
    ->prefix('ai')
    ->name('ai.')
    ->group(function () {
        Route::post('/hotels/{hotel}/review-summary/refresh', [ReviewSummaryController::class, 'refresh'])
            ->name('review-summary.refresh');
    });
