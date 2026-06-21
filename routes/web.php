<?php

use App\Http\Controllers\ProfileController;
use App\Models\Hotel;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $hotels = Hotel::where('status', 'active')
        ->with('amenities')
        ->latest()
        ->take(3)
        ->get();

    return view('welcome', compact('hotels'));
});

Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    if ($user->role === 'owner') {
        return redirect()->route('owner.dashboard');
    }

    return redirect()->route('customer.home');
})->middleware(['auth', 'active'])->name('dashboard');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::patch('/profile/password', [ProfileController::class, 'changePassword'])
        ->name('profile.password.update');

    Route::get('/profile/payment-info', [ProfileController::class, 'paymentInfo'])
        ->name('profile.payment-info');

    Route::patch('/profile/payment-info', [ProfileController::class, 'updatePaymentInfo'])
        ->name('profile.payment-info.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

Route::middleware(['auth'])->get('/account-locked', function () {
    if (auth()->user()->status === 'active') {
        return redirect()->route('dashboard');
    }

    return view('auth.account-locked', [
        'user' => auth()->user()->load('lockedBy'),
    ]);
})->name('account.locked');

require __DIR__.'/auth.php';
require __DIR__.'/customer.php';
require __DIR__.'/owner.php';
require __DIR__.'/admin.php';
require __DIR__.'/ai.php';
