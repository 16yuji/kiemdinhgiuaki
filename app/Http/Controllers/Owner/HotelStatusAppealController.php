<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\HotelStatusAppeal;
use App\Services\SystemLogService;
use Illuminate\Http\Request;

class HotelStatusAppealController extends Controller
{
    public function create(Hotel $hotel)
    {
        $this->authorizeHotel($hotel);

        if ($hotel->status === 'active') {
            return redirect()
                ->route('owner.hotels.show', $hotel)
                ->with('error', 'Khách sạn đang hoạt động nên không cần gửi yêu cầu xem xét.');
        }

        $latestAppeal = HotelStatusAppeal::where('hotel_id', $hotel->id)
            ->where('owner_id', auth()->id())
            ->latest()
            ->first();

        return view('owner.hotels.appeal', compact('hotel', 'latestAppeal'));
    }

    public function store(Request $request, Hotel $hotel)
    {
        $this->authorizeHotel($hotel);

        if ($hotel->status === 'active') {
            return redirect()
                ->route('owner.hotels.show', $hotel)
                ->with('error', 'Khách sạn đang hoạt động nên không cần gửi yêu cầu xem xét.');
        }

        $hasPendingAppeal = HotelStatusAppeal::where('hotel_id', $hotel->id)
            ->where('owner_id', auth()->id())
            ->where('status', 'pending')
            ->exists();

        if ($hasPendingAppeal) {
            return back()->with('error', 'Bạn đã có yêu cầu xem xét đang chờ Admin xử lý.');
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ], [
            'reason.required' => 'Vui lòng nhập nội dung giải trình.',
        ]);

        $appeal = HotelStatusAppeal::create([
            'hotel_id' => $hotel->id,
            'owner_id' => auth()->id(),
            'status' => 'pending',
            'reason' => $data['reason'],
        ]);

        SystemLogService::write(
            'create_hotel_status_appeal',
            'hotels',
            Hotel::class,
            $hotel->id,
            'Owner gửi yêu cầu xem xét trạng thái khách sạn.',
            [
                'hotel_name' => $hotel->name,
                'hotel_status' => $hotel->status,
                'appeal_id' => $appeal->id,
            ]
        );

        return redirect()
            ->route('owner.hotels.show', $hotel)
            ->with('success', 'Gửi yêu cầu xem xét thành công. Vui lòng chờ Admin xử lý.');
    }

    private function authorizeHotel(Hotel $hotel): void
    {
        if ((int) $hotel->owner_id !== (int) auth()->id()) {
            abort(403, 'Bạn không có quyền thao tác trên khách sạn này.');
        }
    }
}