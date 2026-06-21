<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\SystemLogService;
use App\Services\Mail\ReviewMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckOutController extends Controller
{
    public function store(Request $request, Booking $booking, ReviewMailService $reviewMailService)
    {
        $this->authorizeBooking($booking);

        if ($booking->status !== 'staying') {
            return redirect()
                ->route('owner.bookings.show', $booking)
                ->with('error', 'Chỉ đơn đang lưu trú mới được check-out.');
        }

        $data = $request->validate([
            'checkout_note' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($booking->checkout_date->isFuture() && empty($data['checkout_note'])) {
            return back()
                ->withInput()
                ->withErrors([
                    'checkout_note' => 'Khách trả phòng sớm. Vui lòng nhập ghi chú check-out.',
                ]);
        }

        DB::transaction(function () use ($booking, $data) {
            $booking->load(['roomAssignments.room', 'financialTransaction']);

            foreach ($booking->roomAssignments as $assignment) {
                $assignment->update([
                    'released_at' => now(),
                ]);

                if ($assignment->room) {
                    $assignment->room->update([
                        'status' => 'cleaning',
                    ]);
                }
            }

            $booking->update([
                'status' => 'completed',
                'checked_out_at' => now(),
                'checkout_note' => $data['checkout_note'] ?? null,
            ]);

            if ($booking->financialTransaction) {
                $booking->financialTransaction->update([
                    'status' => 'waiting_settlement',
                    'note' => 'Đơn đã hoàn tất, chờ Admin đối soát quyết toán.',
                ]);
            }

            SystemLogService::write(
                'booking_checkout',
                'bookings',
                Booking::class,
                $booking->id,
                'Owner xác nhận check-out, phòng chuyển sang trạng thái đang dọn.',
                [
                    'booking_code' => $booking->booking_code,
                    'checkout_note' => $data['checkout_note'] ?? null,
                    'is_early_checkout' => $booking->checkout_date->isFuture(),
                ]
            );
        });

        $reviewMailService->sendReviewInvitation($booking->fresh());

        return redirect()
            ->route('owner.bookings.show', $booking)
            ->with('success', 'Check-out thành công. Đơn đã hoàn tất, phòng chuyển sang trạng thái Đang dọn.');
    }

    private function authorizeBooking(Booking $booking): void
    {
        if ((int) $booking->hotel->owner_id !== (int) auth()->id()) {
            abort(403, 'Bạn không có quyền thao tác trên đơn này.');
        }
    }
}