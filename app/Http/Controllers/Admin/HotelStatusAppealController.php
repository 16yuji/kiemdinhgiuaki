<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\HotelStatusAppeal;
use App\Services\SystemLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HotelStatusAppealController extends Controller
{
    public function index(Request $request)
    {
        $appeals = HotelStatusAppeal::with(['hotel.owner', 'owner', 'reviewer'])
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->keyword, function ($query, $keyword) {
                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->where('reason', 'like', "%{$keyword}%")
                        ->orWhereHas('hotel', function ($hotelQuery) use ($keyword) {
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

        return view('admin.hotel-appeals.index', compact('appeals'));
    }

    public function show(HotelStatusAppeal $appeal)
    {
        $appeal->load(['hotel.owner', 'owner', 'reviewer']);

        return view('admin.hotel-appeals.show', compact('appeal'));
    }

    public function approve(Request $request, HotelStatusAppeal $appeal)
    {
        if ($appeal->status !== 'pending') {
            return back()->with('error', 'Yêu cầu này đã được xử lý.');
        }

        $data = $request->validate([
            'admin_reply' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($appeal, $data) {
            $appeal->hotel->update([
                'status' => 'active',
                'status_reason' => null,
                'status_changed_at' => now(),
                'status_changed_by' => auth()->id(),
            ]);

            $appeal->update([
                'status' => 'approved',
                'admin_reply' => $data['admin_reply'] ?? 'Admin đã chấp nhận yêu cầu và khôi phục khách sạn.',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            SystemLogService::write(
                'approve_hotel_status_appeal',
                'hotels',
                Hotel::class,
                $appeal->hotel_id,
                'Admin chấp nhận yêu cầu xem xét và khôi phục khách sạn.',
                [
                    'appeal_id' => $appeal->id,
                    'hotel_name' => $appeal->hotel->name,
                    'owner_id' => $appeal->owner_id,
                ]
            );
        });

        return redirect()
            ->route('admin.hotel-appeals.show', $appeal)
            ->with('success', 'Đã chấp nhận yêu cầu và khôi phục khách sạn.');
    }

    public function reject(Request $request, HotelStatusAppeal $appeal)
    {
        if ($appeal->status !== 'pending') {
            return back()->with('error', 'Yêu cầu này đã được xử lý.');
        }

        $data = $request->validate([
            'admin_reply' => ['required', 'string', 'max:2000'],
        ], [
            'admin_reply.required' => 'Vui lòng nhập lý do từ chối.',
        ]);

        DB::transaction(function () use ($appeal, $data) {
            $appeal->update([
                'status' => 'rejected',
                'admin_reply' => $data['admin_reply'],
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            SystemLogService::write(
                'reject_hotel_status_appeal',
                'hotels',
                Hotel::class,
                $appeal->hotel_id,
                'Admin từ chối yêu cầu xem xét trạng thái khách sạn.',
                [
                    'appeal_id' => $appeal->id,
                    'hotel_name' => $appeal->hotel->name,
                    'owner_id' => $appeal->owner_id,
                    'admin_reply' => $data['admin_reply'],
                ]
            );
        });

        return redirect()
            ->route('admin.hotel-appeals.show', $appeal)
            ->with('success', 'Đã từ chối yêu cầu xem xét.');
    }
}