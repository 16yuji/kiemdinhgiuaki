<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Review;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'userCount' => User::count(),
            'hotelCount' => Hotel::count(),
            'bookingCount' => Booking::count(),
            'reviewCount' => Review::count(),
        ]);
    }
}