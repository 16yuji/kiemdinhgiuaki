<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Services\SystemLogService;
use Illuminate\Http\Request;

class HotelModerationController extends Controller
{
    public function index(Request $request)
    {
        $hotels = Hotel::with(['owner'])
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->keyword, function ($query, $keyword) {
                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->where('name', 'like', "%{$keyword}%")
                        ->orWhere('province', 'like', "%{$keyword}%")
                        ->orWhere('district', 'like', "%{$keyword}%")
                        ->orWhere('address', 'like', "%{$keyword}%")
                        ->orWhereHas('owner', function ($ownerQuery) use ($keyword) {
                            $ownerQuery->where('name', 'like', "%{$keyword}%")
                                ->orWhere('email', 'like', "%{$keyword}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.hotels.index', compact('hotels'));
    }

    public function show(Hotel $hotel)
    {
        $hotel->load([
            'owner',
            'roomTypes.rooms',
            'reviews',
            'statusChangedBy',
        ]);

        return view('admin.hotels.show', compact('hotel'));
    }

    public function statusForm(Hotel $hotel)
    {
        $hotel->load(['owner', 'statusChangedBy']);

        return view('admin.hotels.status', compact('hotel'));
    }

    public function updateStatus(Request $request, Hotel $hotel)
    {
        $data = $request->validate([
            'status' => ['required', 'in:active,hidden,locked'],
            'status_reason' => ['nullable', 'string', 'max:1000'],
        ], [
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
        ]);

        if (in_array($data['status'], ['hidden', 'locked'], true) && empty($data['status_reason'])) {
            return back()
                ->withInput()
                ->withErrors([
                    'status_reason' => 'Vui lòng nhập lý do khi ẩn hoặc khóa khách sạn.',
                ]);
        }

        $oldStatus = $hotel->status;

        $hotel->update([
            'status' => $data['status'],
            'status_reason' => $data['status'] === 'active' ? null : $data['status_reason'],
            'status_changed_at' => now(),
            'status_changed_by' => auth()->id(),
        ]);

        SystemLogService::write(
            'update_hotel_status',
            'hotels',
            Hotel::class,
            $hotel->id,
            'Admin thay đổi trạng thái cơ sở lưu trú.',
            [
                'hotel_name' => $hotel->name,
                'old_status' => $oldStatus,
                'new_status' => $data['status'],
                'reason' => $data['status_reason'] ?? null,
            ]
        );

        return redirect()
            ->route('admin.hotels.show', $hotel)
            ->with('success', 'Cập nhật trạng thái khách sạn thành công.');
    }
}