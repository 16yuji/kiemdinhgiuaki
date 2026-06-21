<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PartnerRequest;
use App\Services\SystemLogService;
use App\Services\Mail\PartnerMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartnerRequestController extends Controller
{
    public function index(Request $request)
    {
        $partnerRequests = PartnerRequest::with(['user', 'reviewer'])
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->keyword, function ($query, $keyword) {
                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->where('business_name', 'like', "%{$keyword}%")
                        ->orWhere('contact_email', 'like', "%{$keyword}%")
                        ->orWhere('contact_phone', 'like', "%{$keyword}%")
                        ->orWhereHas('user', function ($userQuery) use ($keyword) {
                            $userQuery->where('name', 'like', "%{$keyword}%")
                                ->orWhere('email', 'like', "%{$keyword}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.partner-requests.index', compact('partnerRequests'));
    }

    public function show(PartnerRequest $partnerRequest)
    {
        $partnerRequest->load(['user', 'reviewer']);

        return view('admin.partner-requests.show', compact('partnerRequest'));
    }

    public function approve(PartnerRequest $partnerRequest, PartnerMailService $partnerMailService)
    {
        if ($partnerRequest->status !== 'pending') {
            return back()->with('error', 'Yêu cầu này đã được xử lý trước đó.');
        }

        DB::transaction(function () use ($partnerRequest) {
            $partnerRequest->update([
                'status' => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'reject_reason' => null,
            ]);

            $partnerRequest->user->update([
                'role' => 'owner',
                'status' => 'active',
            ]);

            SystemLogService::write(
                'approve_partner_request',
                'partner_requests',
                PartnerRequest::class,
                $partnerRequest->id,
                'Admin duyệt yêu cầu trở thành đối tác.',
                [
                    'user_id' => $partnerRequest->user_id,
                    'business_name' => $partnerRequest->business_name,
                ]
            );
        });

        $partnerMailService->sendApproved($partnerRequest->fresh());

        return redirect()
            ->route('admin.partner-requests.index')
            ->with('success', 'Duyệt yêu cầu thành công. Người dùng đã trở thành Owner.');
    }

    public function reject(Request $request, PartnerRequest $partnerRequest, PartnerMailService $partnerMailService)
    {
        if ($partnerRequest->status !== 'pending') {
            return back()->with('error', 'Yêu cầu này đã được xử lý trước đó.');
        }

        $data = $request->validate([
            'reject_reason' => ['required', 'string', 'max:1000'],
        ], [
            'reject_reason.required' => 'Vui lòng nhập lý do từ chối.',
        ]);

        $partnerRequest->update([
            'status' => 'rejected',
            'reject_reason' => $data['reject_reason'],
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        SystemLogService::write(
            'reject_partner_request',
            'partner_requests',
            PartnerRequest::class,
            $partnerRequest->id,
            'Admin từ chối yêu cầu trở thành đối tác.',
            [
                'user_id' => $partnerRequest->user_id,
                'business_name' => $partnerRequest->business_name,
                'reject_reason' => $data['reject_reason'],
            ]
        );

        $partnerMailService->sendRejected($partnerRequest->fresh());

        return redirect()
            ->route('admin.partner-requests.index')
            ->with('success', 'Từ chối yêu cầu đối tác thành công.');
    }
}