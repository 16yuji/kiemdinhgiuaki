<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        $hotelIds = Hotel::where('owner_id', auth()->id())->pluck('id');

        $rooms = Room::with(['hotel', 'roomType'])
            ->whereIn('hotel_id', $hotelIds)
            ->latest()
            ->paginate(10);

        return view('owner.rooms.index', compact('rooms'));
    }

    public function create()
    {
        $hotels = Hotel::where('owner_id', auth()->id())
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $roomTypes = RoomType::with('hotel')
            ->whereIn('hotel_id', $hotels->pluck('id'))
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('owner.rooms.create', compact('hotels', 'roomTypes'));
    }

    public function store(Request $request)
    {
        $hotel = $this->getOwnerHotel((int) $request->input('hotel_id'));
        $roomType = $this->getOwnerRoomType((int) $request->input('room_type_id'));

        if ((int) $roomType->hotel_id !== (int) $hotel->id) {
            return back()
                ->withInput()
                ->with('error', 'Hạng phòng không thuộc khách sạn đã chọn.');
        }

        $data = $request->validate([
            'hotel_id' => ['required', 'exists:hotels,id'],
            'room_type_id' => ['required', 'exists:room_types,id'],
            'room_number' => ['required', 'string', 'max:50'],
            'floor' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:available,occupied,cleaning,maintenance,locked'],
            'note' => ['nullable', 'string'],
        ], [
            'hotel_id.required' => 'Vui lòng chọn khách sạn.',
            'room_type_id.required' => 'Vui lòng chọn hạng phòng.',
            'room_number.required' => 'Vui lòng nhập số phòng.',
            'status.required' => 'Vui lòng chọn trạng thái phòng.',
        ]);

        $exists = Room::where('hotel_id', $hotel->id)
            ->where('room_number', $data['room_number'])
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors([
                    'room_number' => 'Số phòng đã tồn tại trong khách sạn này.',
                ]);
        }

        Room::create($data);

        return redirect()
            ->route('owner.rooms.index')
            ->with('success', 'Thêm phòng thành công.');
    }

    public function show(Room $room)
    {
        $this->authorizeRoom($room);

        $room->load(['hotel', 'roomType']);

        return view('owner.rooms.show', compact('room'));
    }

    public function edit(Room $room)
    {
        $this->authorizeRoom($room);

        $hotels = Hotel::where('owner_id', auth()->id())
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $roomTypes = RoomType::with('hotel')
            ->whereIn('hotel_id', $hotels->pluck('id'))
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('owner.rooms.edit', compact('room', 'hotels', 'roomTypes'));
    }

    public function update(Request $request, Room $room)
    {
        $this->authorizeRoom($room);

        $hotel = $this->getOwnerHotel((int) $request->input('hotel_id'));
        $roomType = $this->getOwnerRoomType((int) $request->input('room_type_id'));

        if ((int) $roomType->hotel_id !== (int) $hotel->id) {
            return back()
                ->withInput()
                ->with('error', 'Hạng phòng không thuộc khách sạn đã chọn.');
        }

        $data = $request->validate([
            'hotel_id' => ['required', 'exists:hotels,id'],
            'room_type_id' => ['required', 'exists:room_types,id'],
            'room_number' => ['required', 'string', 'max:50'],
            'floor' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:available,occupied,cleaning,maintenance,locked'],
            'note' => ['nullable', 'string'],
        ], [
            'hotel_id.required' => 'Vui lòng chọn khách sạn.',
            'room_type_id.required' => 'Vui lòng chọn hạng phòng.',
            'room_number.required' => 'Vui lòng nhập số phòng.',
            'status.required' => 'Vui lòng chọn trạng thái phòng.',
        ]);

        $exists = Room::where('hotel_id', $hotel->id)
            ->where('room_number', $data['room_number'])
            ->where('id', '!=', $room->id)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors([
                    'room_number' => 'Số phòng đã tồn tại trong khách sạn này.',
                ]);
        }

        $room->update($data);

        return redirect()
            ->route('owner.rooms.index')
            ->with('success', 'Cập nhật phòng thành công.');
    }

    public function destroy(Room $room)
    {
        $this->authorizeRoom($room);

        if ($room->assignments()->exists()) {
            return redirect()
                ->route('owner.rooms.index')
                ->with('error', 'Không thể xóa phòng đã từng được gán cho đơn đặt phòng.');
        }

        $room->delete();

        return redirect()
            ->route('owner.rooms.index')
            ->with('success', 'Xóa phòng thành công.');
    }

    private function getOwnerHotel(int $hotelId): Hotel
    {
        return Hotel::where('owner_id', auth()->id())
            ->where('id', $hotelId)
            ->firstOrFail();
    }

    private function getOwnerRoomType(int $roomTypeId): RoomType
    {
        return RoomType::whereHas('hotel', function ($query) {
                $query->where('owner_id', auth()->id());
            })
            ->where('id', $roomTypeId)
            ->firstOrFail();
    }

    private function authorizeRoom(Room $room): void
    {
        if ((int) $room->hotel->owner_id !== (int) auth()->id()) {
            abort(403, 'Bạn không có quyền thao tác trên phòng này.');
        }
    }
}