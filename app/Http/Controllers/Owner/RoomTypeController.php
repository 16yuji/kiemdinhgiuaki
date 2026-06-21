<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\Hotel;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RoomTypeController extends Controller
{
    public function index()
    {
        $hotelIds = Hotel::where('owner_id', auth()->id())->pluck('id');

        $roomTypes = RoomType::with(['hotel', 'rooms'])
            ->whereIn('hotel_id', $hotelIds)
            ->latest()
            ->paginate(10);

        return view('owner.room-types.index', compact('roomTypes'));
    }

    public function create()
    {
        $hotels = Hotel::where('owner_id', auth()->id())
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $amenities = Amenity::where('type', 'room_type')
            ->orderBy('name')
            ->get();

        return view('owner.room-types.create', compact('hotels', 'amenities'));
    }

    public function store(Request $request)
    {
        $hotel = $this->getOwnerHotel((int) $request->input('hotel_id'));

        $data = $request->validate([
            'hotel_id' => ['required', 'exists:hotels,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'max_guests' => ['required', 'integer', 'min:1'],
            'bed_type' => ['nullable', 'string', 'max:255'],
            'area' => ['nullable', 'numeric', 'min:1'],
            'price_per_night' => ['required', 'numeric', 'min:1'],
            'status' => ['required', 'in:active,hidden'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['exists:amenities,id'],
        ], [
            'hotel_id.required' => 'Vui lòng chọn khách sạn.',
            'name.required' => 'Vui lòng nhập tên hạng phòng.',
            'max_guests.required' => 'Vui lòng nhập sức chứa tối đa.',
            'price_per_night.required' => 'Vui lòng nhập giá một đêm.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'thumbnail.image' => 'Ảnh đại diện không hợp lệ.',
            'thumbnail.max' => 'Ảnh đại diện không được vượt quá 2MB.',
        ]);

        $data['hotel_id'] = $hotel->id;

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('room-types', 'public');
        }

        $roomType = RoomType::create($data);
        $roomType->amenities()->sync($request->input('amenities', []));

        return redirect()
            ->route('owner.room-types.index')
            ->with('success', 'Thêm hạng phòng thành công.');
    }

    public function show(RoomType $roomType)
    {
        $this->authorizeRoomType($roomType);

        $roomType->load(['hotel', 'amenities', 'rooms']);

        return view('owner.room-types.show', compact('roomType'));
    }

    public function edit(RoomType $roomType)
    {
        $this->authorizeRoomType($roomType);

        $hotels = Hotel::where('owner_id', auth()->id())
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $amenities = Amenity::where('type', 'room_type')
            ->orderBy('name')
            ->get();

        $roomType->load('amenities');

        return view('owner.room-types.edit', compact('roomType', 'hotels', 'amenities'));
    }

    public function update(Request $request, RoomType $roomType)
    {
        $this->authorizeRoomType($roomType);

        $hotel = $this->getOwnerHotel((int) $request->input('hotel_id'));

        $data = $request->validate([
            'hotel_id' => ['required', 'exists:hotels,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'max_guests' => ['required', 'integer', 'min:1'],
            'bed_type' => ['nullable', 'string', 'max:255'],
            'area' => ['nullable', 'numeric', 'min:1'],
            'price_per_night' => ['required', 'numeric', 'min:1'],
            'status' => ['required', 'in:active,hidden'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['exists:amenities,id'],
        ], [
            'hotel_id.required' => 'Vui lòng chọn khách sạn.',
            'name.required' => 'Vui lòng nhập tên hạng phòng.',
            'max_guests.required' => 'Vui lòng nhập sức chứa tối đa.',
            'price_per_night.required' => 'Vui lòng nhập giá một đêm.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'thumbnail.image' => 'Ảnh đại diện không hợp lệ.',
            'thumbnail.max' => 'Ảnh đại diện không được vượt quá 2MB.',
        ]);

        $data['hotel_id'] = $hotel->id;

        if ($request->hasFile('thumbnail')) {
            if ($roomType->thumbnail) {
                Storage::disk('public')->delete($roomType->thumbnail);
            }

            $data['thumbnail'] = $request->file('thumbnail')->store('room-types', 'public');
        }

        $roomType->update($data);
        $roomType->amenities()->sync($request->input('amenities', []));

        return redirect()
            ->route('owner.room-types.index')
            ->with('success', 'Cập nhật hạng phòng thành công.');
    }

    public function destroy(RoomType $roomType)
    {
        $this->authorizeRoomType($roomType);

        if ($roomType->rooms()->exists() || $roomType->bookingRoomTypes()->exists()) {
            return redirect()
                ->route('owner.room-types.index')
                ->with('error', 'Không thể xóa hạng phòng đã có phòng cụ thể hoặc đơn đặt phòng liên quan.');
        }

        if ($roomType->thumbnail) {
            Storage::disk('public')->delete($roomType->thumbnail);
        }

        $roomType->amenities()->detach();
        $roomType->delete();

        return redirect()
            ->route('owner.room-types.index')
            ->with('success', 'Xóa hạng phòng thành công.');
    }

    private function getOwnerHotel(int $hotelId): Hotel
    {
        return Hotel::where('owner_id', auth()->id())
            ->where('id', $hotelId)
            ->firstOrFail();
    }

    private function authorizeRoomType(RoomType $roomType): void
    {
        if ((int) $roomType->hotel->owner_id !== (int) auth()->id()) {
            abort(403, 'Bạn không có quyền thao tác trên hạng phòng này.');
        }
    }
}