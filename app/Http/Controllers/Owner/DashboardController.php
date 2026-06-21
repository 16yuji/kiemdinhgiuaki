<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomType;

class DashboardController extends Controller
{
    public function index()
    {
        $ownerId = auth()->id();

        $hotelIds = Hotel::where('owner_id', $ownerId)->pluck('id');

        return view('owner.dashboard', [
            'hotelCount' => Hotel::where('owner_id', $ownerId)->count(),
            'roomTypeCount' => RoomType::whereIn('hotel_id', $hotelIds)->count(),
            'roomCount' => Room::whereIn('hotel_id', $hotelIds)->count(),
            'bookingCount' => Booking::whereIn('hotel_id', $hotelIds)->count(),
        ]);
    }
}