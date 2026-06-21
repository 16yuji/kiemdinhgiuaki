<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\FinancialTransaction;
use App\Models\OwnerAdjustment;
use App\Models\Settlement;
use Illuminate\Http\Request;

class RevenueController extends Controller
{
    public function index(Request $request)
    {
        $transactions = FinancialTransaction::with([
                'booking.hotel',
                'settlement.appliedAdjustments',
            ])
            ->where('owner_id', auth()->id())
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $summaryQuery = FinancialTransaction::where('owner_id', auth()->id());

        $totalGross = (clone $summaryQuery)->sum('gross_amount');
        $totalFee = (clone $summaryQuery)->sum('platform_fee');
        $totalOwnerAmount = (clone $summaryQuery)->sum('owner_amount');
        $settledAmount = Settlement::where('owner_id', auth()->id())
            ->where('status', 'settled')
            ->sum('amount');
        $waitingAmount = (clone $summaryQuery)
            ->whereIn('status', ['waiting_settlement', 'temporary_recorded'])
            ->sum('owner_amount');

        $pendingAdjustmentAmount = OwnerAdjustment::where('owner_id', auth()->id())
            ->pending()
            ->sum('remaining_amount');

        $deductedAdjustmentAmount = OwnerAdjustment::where('owner_id', auth()->id())
            ->where('status', 'deducted')
            ->sum('amount');

        $adjustments = OwnerAdjustment::with(['booking.hotel', 'financialTransaction.booking', 'appliedSettlement'])
            ->where('owner_id', auth()->id())
            ->latest()
            ->paginate(5, ['*'], 'adjustments_page')
            ->withQueryString();

        $estimatedNetWaitingAmount = max(0, (float) $waitingAmount - (float) $pendingAdjustmentAmount);

        return view('owner.revenues.index', compact(
            'transactions',
            'totalGross',
            'totalFee',
            'totalOwnerAmount',
            'settledAmount',
            'waitingAmount',
            'pendingAdjustmentAmount',
            'deductedAdjustmentAmount',
            'estimatedNetWaitingAmount',
            'adjustments'
        ));
    }
}
