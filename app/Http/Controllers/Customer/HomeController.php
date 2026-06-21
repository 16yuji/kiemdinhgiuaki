<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Hotel;

class HomeController extends Controller
{
    public function index()
    {
        $hotels = Hotel::with(['roomTypes'])
            ->where('status', 'active')
            ->latest()
            ->take(6)
            ->get();

        return view('customer.home', compact('hotels'));
    }
}