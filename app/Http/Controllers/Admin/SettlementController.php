<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinancialTransaction;
use App\Models\OwnerAdjustment;
use App\Models\Settlement;
use App\Services\SystemLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettlementController extends Controller
{
    public function index(Request $request)
    {
        $transactions = FinancialTransaction::with([
                'booking.hotel',
                'booking.customer',
                'owner',
                'settlement',
            ])
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->keyword, function ($query, $keyword) {
                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->whereHas('booking', function ($bookingQuery) use ($keyword) {
                        $bookingQuery->where('booking_code', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('booking.hotel', function ($hotelQuery) use ($keyword) {
                        $hotelQuery->where('name', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('owner', function ($ownerQuery) use ($keyword) {
                        $ownerQuery->where('name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%");
                    });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $pendingAdjustmentsByOwner = OwnerAdjustment::query()
            ->whereIn('owner_id', $transactions->getCollection()->pluck('owner_id')->unique())
            ->pending()
            ->selectRaw('owner_id, SUM(remaining_amount) as total_remaining')
            ->groupBy('owner_id')
            ->pluck('total_remaining', 'owner_id');

        return view('admin.settlements.index', compact('transactions', 'pendingAdjustmentsByOwner'));
    }

    public function show(FinancialTransaction $financialTransaction)
    {
        $financialTransaction->load([
            'booking.hotel',
            'booking.customer',
            'booking.payment',
            'owner',
            'settlement.admin',
            'settlement.appliedAdjustments.booking',
        ]);

        $pendingAdjustments = OwnerAdjustment::with(['booking', 'financialTransaction.booking'])
            ->where('owner_id', $financialTransaction->owner_id)
            ->pending()
            ->oldest()
            ->get();

        $pendingAdjustmentAmount = $pendingAdjustments->sum('remaining_amount');
        $estimatedTransferAmount = max(
            0,
            (float) $financialTransaction->owner_amount - (float) $pendingAdjustmentAmount
        );

        return view('admin.settlements.show', [
            'transaction' => $financialTransaction,
            'pendingAdjustments' => $pendingAdjustments,
            'pendingAdjustmentAmount' => $pendingAdjustmentAmount,
            'estimatedTransferAmount' => $estimatedTransferAmount,
        ]);
    }

    public function confirm(Request $request, FinancialTransaction $financialTransaction)
    {
        $financialTransaction->load(['booking.hotel', 'owner', 'settlement']);

        if ($financialTransaction->status === 'settled') {
            return back()->with('error', 'Giao dịch này đã được đối soát trước đó.');
        }

        if (!in_array($financialTransaction->status, ['waiting_settlement', 'temporary_recorded'], true)) {
            return back()->with('error', 'Giao dịch này chưa đủ điều kiện đối soát.');
        }

        $owner = $financialTransaction->owner;
        if (
            !$owner ||
            blank($owner->bank_name) ||
            blank($owner->bank_account_number) ||
            blank($owner->bank_account_name)
        ) {
            return back()->with('error', 'Owner chưa cập nhật đầy đủ thông tin ngân hàng. Vui lòng yêu cầu Owner cập nhật trước khi xác nhận quyết toán.');
        }

        $data = $request->validate([
            'transfer_code' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'transfer_code.required' => 'Vui lòng nhập mã giao dịch chuyển khoản.',
        ]);

        try {
            DB::transaction(function () use ($financialTransaction, $data) {
            $financialTransaction = FinancialTransaction::with(['booking', 'owner'])
                ->whereKey($financialTransaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($financialTransaction->status === 'settled') {
                throw new \RuntimeException('Giao dịch này đã được đối soát trước đó.');
            }

            if (!in_array($financialTransaction->status, ['waiting_settlement', 'temporary_recorded'], true)) {
                throw new \RuntimeException('Giao dịch này chưa đủ điều kiện đối soát.');
            }

            $remainingSettlementAmount = (float) $financialTransaction->owner_amount;
            $deductedAmount = 0.0;
            $deductedNotes = [];
            $adjustmentsToApply = OwnerAdjustment::where('owner_id', $financialTransaction->owner_id)
                ->pending()
                ->oldest()
                ->lockForUpdate()
                ->get();

            $settlement = Settlement::updateOrCreate(
                ['financial_transaction_id' => $financialTransaction->id],
                [
                    'owner_id' => $financialTransaction->owner_id,
                    'admin_id' => auth()->id(),
                    'amount' => $financialTransaction->owner_amount,
                    'status' => 'settled',
                    'bank_name' => $financialTransaction->owner->bank_name,
                    'bank_account_number' => $financialTransaction->owner->bank_account_number,
                    'bank_account_name' => $financialTransaction->owner->bank_account_name,
                    'transfer_code' => $data['transfer_code'],
                    'note' => $data['note'] ?? null,
                    'settled_at' => now(),
                ]
            );

            foreach ($adjustmentsToApply as $adjustment) {
                if ($remainingSettlementAmount <= 0) {
                    break;
                }

                $deduction = min((float) $adjustment->remaining_amount, $remainingSettlementAmount);
                if ($deduction <= 0) {
                    continue;
                }

                $newRemaining = (float) $adjustment->remaining_amount - $deduction;
                $remainingSettlementAmount -= $deduction;
                $deductedAmount += $deduction;
                $deductedNotes[] = 'Trừ ' . number_format($deduction, 0, ',', '.') . 'đ từ điều chỉnh #' . $adjustment->id;

                $adjustment->update([
                    'remaining_amount' => $newRemaining,
                    'status' => $newRemaining <= 0 ? 'deducted' : 'pending_deduction',
                    'applied_settlement_id' => $settlement->id,
                    'deducted_at' => $newRemaining <= 0 ? now() : null,
                ]);
            }

            $noteParts = [];
            if (!empty($data['note'])) {
                $noteParts[] = $data['note'];
            }
            if ($deductedAmount > 0) {
                $noteParts[] = 'Đã trừ công nợ Owner: ' . number_format($deductedAmount, 0, ',', '.') . 'đ.';
                $noteParts[] = implode('; ', $deductedNotes);
            }

            $settlement->update([
                'amount' => max(0, $remainingSettlementAmount),
                'note' => implode("\n", array_filter($noteParts)) ?: null,
            ]);

            $financialTransaction->update([
                'status' => 'settled',
                'note' => $deductedAmount > 0
                    ? 'Admin đã xác nhận đối soát. Số tiền chuyển thực tế đã trừ công nợ Owner.'
                    : 'Admin đã xác nhận chuyển tiền cho Owner.',
            ]);

            SystemLogService::write(
                'confirm_settlement',
                'settlements',
                FinancialTransaction::class,
                $financialTransaction->id,
                'Admin xác nhận đối soát doanh thu cho Owner.',
                [
                    'booking_code' => $financialTransaction->booking->booking_code ?? null,
                    'owner_id' => $financialTransaction->owner_id,
                    'owner_amount' => $financialTransaction->owner_amount,
                    'deducted_adjustments' => $deductedAmount,
                    'actual_transfer_amount' => $settlement->amount,
                    'transfer_code' => $data['transfer_code'],
                ]
            );
            });
        } catch (\RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.settlements.show', $financialTransaction)
            ->with('success', 'Xác nhận đối soát thành công. Nếu Owner có công nợ hoàn tiền, hệ thống đã tự trừ vào số tiền chuyển kỳ này.');
    }
}
