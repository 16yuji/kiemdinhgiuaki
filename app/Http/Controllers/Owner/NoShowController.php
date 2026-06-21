<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\SystemLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NoShowController extends Controller
{
    public function store(Request $request, Booking $booking)
    {
        $this->authorizeBooking($booking);

        if ($booking->status !== 'confirmed') {
            return redirect()
                ->route('owner.bookings.show', $booking)
                ->with('error', 'Chỉ đơn đã xác nhận mới có thể ghi nhận No-show.');
        }

        if ($booking->checkin_date->isFuture()) {
            return redirect()
                ->route('owner.bookings.show', $booking)
                ->with('error', 'Chưa đến ngày nhận phòng nên không thể ghi nhận No-show.');
        }

        $data = $request->validate([
            'no_show_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($booking, $data) {
            $booking->load(['financialTransaction']);

            $booking->update([
                'status' => 'no_show',
                'no_show_reason' => $data['no_show_reason'] ?? 'Khách không đến nhận phòng.',
                'no_show_at' => now(),
            ]);

            if ($booking->financialTransaction) {
                $booking->financialTransaction->update([
                    'status' => 'postponed',
                    'note' => 'Đơn No-show, cần Admin kiểm tra chính sách xử lý.',
                ]);
            }

            SystemLogService::write(
                'booking_no_show',
                'bookings',
                Booking::class,
                $booking->id,
                'Owner ghi nhận khách không đến nhận phòng.',
                [
                    'booking_code' => $booking->booking_code,
                    'no_show_reason' => $data['no_show_reason'] ?? 'Khách không đến nhận phòng.',
                ]
            );
        });

        return redirect()
            ->route('owner.bookings.show', $booking)
            ->with('success', 'Đã ghi nhận khách không đến/No-show.');
    }

    private function authorizeBooking(Booking $booking): void
    {
        if ((int) $booking->hotel->owner_id !== (int) auth()->id()) {
            abort(403, 'Bạn không có quyền thao tác trên đơn này.');
        }
    }
}