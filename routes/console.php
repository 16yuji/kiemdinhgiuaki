<?php

use App\Models\Booking;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('bookings:expire-pending', function () {
    $expiredBookings = Booking::where('status', 'pending_payment')
        ->whereNotNull('hold_expires_at')
        ->where('hold_expires_at', '<', now())
        ->get();

    $count = 0;

    foreach ($expiredBookings as $booking) {
        $booking->load(['payment']);

        $booking->update([
            'status' => 'payment_expired',
        ]);

        if ($booking->payment && $booking->payment->status === 'pending') {
            $booking->payment->update([
                'status' => 'expired',
            ]);
        }

        $count++;
    }

    $this->info("Đã hết hạn {$count} đơn chờ thanh toán.");
})->purpose('Chuyển các đơn pending_payment quá hạn giữ phòng sang payment_expired.');


Schedule::command('bookings:expire-pending')
    ->everyMinute()
    ->withoutOverlapping();