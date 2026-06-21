<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OwnerAdjustment;
use App\Models\Payment;
use App\Services\SystemLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RefundController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::with([
                'booking.customer',
                'booking.hotel.owner',
                'refundedBy',
            ])
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            }, function ($query) {
                $query->whereIn('status', ['refunding', 'refunded', 'non_refundable']);
            })
            ->when($request->keyword, function ($query, $keyword) {
                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->where('transaction_code', 'like', "%{$keyword}%")
                        ->orWhereHas('booking', function ($bookingQuery) use ($keyword) {
                            $bookingQuery->where('booking_code', 'like', "%{$keyword}%")
                                ->orWhere('contact_name', 'like', "%{$keyword}%")
                                ->orWhere('contact_phone', 'like', "%{$keyword}%");
                        })
                        ->orWhereHas('booking.hotel', function ($hotelQuery) use ($keyword) {
                            $hotelQuery->where('name', 'like', "%{$keyword}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.refunds.index', compact('payments'));
    }

    public function show(Payment $payment)
    {
        $payment->load([
            'booking.customer',
            'booking.hotel.owner',
            'booking.financialTransaction',
            'booking.roomTypes.roomType',
            'refundedBy',
        ]);

        return view('admin.refunds.show', compact('payment'));
    }

    public function markRefunded(Request $request, Payment $payment)
    {
        if ($payment->status !== 'refunding') {
            return back()->with('error', 'Chỉ yêu cầu đang chờ hoàn tiền mới được xác nhận đã hoàn.');
        }

        $data = $request->validate([
            'refund_amount' => ['required', 'numeric', 'min:0', 'max:' . $payment->amount],
            'refund_note' => ['nullable', 'string', 'max:1000'],
        ], [
            'refund_amount.required' => 'Vui lòng nhập số tiền đã hoàn.',
            'refund_amount.max' => 'Số tiền hoàn không được lớn hơn số tiền thanh toán.',
        ]);

        try {
            DB::transaction(function () use ($payment, $data) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($payment->status !== 'refunding') {
                throw new \RuntimeException('Yêu cầu hoàn tiền này đã được xử lý trước đó.');
            }

            $payment->load(['booking.hotel.owner', 'booking.financialTransaction']);

            $booking = $payment->booking;
            $financialTransaction = $booking?->financialTransaction;
            $isSettled = $financialTransaction && $financialTransaction->status === 'settled';
            $refundNote = $data['refund_note'] ?? 'Admin đã xác nhận hoàn tiền thủ công.';
            $ownerClawbackAmount = 0;

            $payment->update([
                'status' => 'refunded',
                'refund_amount' => $data['refund_amount'],
                'refund_note' => $refundNote,
                'refunded_at' => now(),
                'refunded_by' => auth()->id(),
            ]);

            if ($financialTransaction && !$isSettled) {
                $financialTransaction->update([
                    'status' => 'adjusted',
                    'note' => 'Admin đã hoàn tiền cho khách trước khi giao dịch được settlement.',
                ]);
            }

            if ($financialTransaction && $isSettled && $booking?->hotel?->owner_id) {
                $ownerClawbackAmount = $this->calculateOwnerClawbackAmount(
                    (float) $data['refund_amount'],
                    (float) $financialTransaction->gross_amount,
                    (float) $financialTransaction->owner_amount
                );

                if ($ownerClawbackAmount > 0) {
                    OwnerAdjustment::firstOrCreate(
                        [
                            'booking_id' => $booking->id,
                            'financial_transaction_id' => $financialTransaction->id,
                            'type' => 'refund_clawback',
                        ],
                        [
                            'owner_id' => $booking->hotel->owner_id,
                            'amount' => $ownerClawbackAmount,
                            'remaining_amount' => $ownerClawbackAmount,
                            'status' => 'pending_deduction',
                            'created_by' => auth()->id(),
                            'reason' => $refundNote,
                        ]
                    );
                }
            }

            SystemLogService::write(
                'mark_payment_refunded',
                'refunds',
                Payment::class,
                $payment->id,
                'Admin xác nhận đã hoàn tiền cho khách.',
                [
                    'booking_code' => $payment->booking->booking_code ?? null,
                    'refund_amount' => $data['refund_amount'],
                    'financial_transaction_status' => $financialTransaction?->status,
                    'owner_adjustment_amount' => $ownerClawbackAmount,
                ]
            );
            });
        } catch (\RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.refunds.show', $payment)
            ->with('success', 'Đã xác nhận hoàn tiền.');
    }

    private function calculateOwnerClawbackAmount(float $refundAmount, float $grossAmount, float $ownerAmount): float
    {
        if ($refundAmount <= 0 || $grossAmount <= 0 || $ownerAmount <= 0) {
            return 0;
        }

        return round(min($ownerAmount, ($refundAmount / $grossAmount) * $ownerAmount), 2);
    }

    public function markNonRefundable(Request $request, Payment $payment)
    {
        if ($payment->status !== 'refunding') {
            return back()->with('error', 'Chỉ yêu cầu đang chờ hoàn tiền mới được xử lý không hoàn.');
        }

        $data = $request->validate([
            'refund_note' => ['required', 'string', 'max:1000'],
        ], [
            'refund_note.required' => 'Vui lòng nhập lý do không hoàn tiền.',
        ]);

        try {
            DB::transaction(function () use ($payment, $data) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($payment->status !== 'refunding') {
                throw new \RuntimeException('Yêu cầu hoàn tiền này đã được xử lý trước đó.');
            }

            $payment->update([
                'status' => 'non_refundable',
                'refund_amount' => 0,
                'refund_note' => $data['refund_note'],
                'refunded_at' => now(),
                'refunded_by' => auth()->id(),
            ]);

            SystemLogService::write(
                'mark_payment_non_refundable',
                'refunds',
                Payment::class,
                $payment->id,
                'Admin xác nhận đơn không được hoàn tiền.',
                [
                    'booking_code' => $payment->booking->booking_code ?? null,
                    'reason' => $data['refund_note'],
                ]
            );
            });
        } catch (\RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.refunds.show', $payment)
            ->with('success', 'Đã cập nhật trạng thái không hoàn tiền.');
    }
}
