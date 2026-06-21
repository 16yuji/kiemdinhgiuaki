<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Services\Ai\ReviewSummaryService;
use App\Services\Booking\AvailabilityService;
use Illuminate\Http\Request;

class HotelDetailController extends Controller
{
    public function show(Request $request, Hotel $hotel, AvailabilityService $availabilityService, ReviewSummaryService $reviewSummaryService)
    {
        if ($hotel->status !== 'active') {
            abort(404);
        }

        $data = $request->validate([
            'checkin_date' => ['nullable', 'date', 'after_or_equal:today'],
            'checkout_date' => ['nullable', 'date', 'after:checkin_date'],
            'guests' => ['nullable', 'integer', 'min:1'],
        ], [
            'checkin_date.after_or_equal' => 'Ngày nhận phòng không được nhỏ hơn ngày hiện tại.',
            'checkout_date.after' => 'Ngày trả phòng phải sau ngày nhận phòng.',
        ]);

        $checkinDate = $data['checkin_date'] ?? now()->toDateString();
        $checkoutDate = $data['checkout_date'] ?? now()->addDay()->toDateString();
        $guests = (int) ($data['guests'] ?? 1);

        $hotel->load([
            'amenities',
            'roomTypes.amenities',
            'roomTypes.rooms',
            'reviews.customer',
            'reviews.reply',
            'reviewSummary',
            'images',
        ]);

        $roomTypes = $hotel->roomTypes
            ->where('status', 'active')
            ->filter(function ($roomType) use ($guests) {
                return $roomType->max_guests >= $guests;
            })
            ->map(function ($roomType) use ($availabilityService, $checkinDate, $checkoutDate) {
                $roomType->available_count = $availabilityService->availableRoomCount(
                    $roomType,
                    $checkinDate,
                    $checkoutDate
                );

                return $roomType;
            });

        $reviewSummary = $reviewSummaryService->summaryForHotel($hotel);

        return view('customer.hotels.show', [
            'hotel' => $hotel,
            'roomTypes' => $roomTypes,
            'checkinDate' => $checkinDate,
            'checkoutDate' => $checkoutDate,
            'guests' => $guests,
            'reviewSummary' => $reviewSummary,
        ]);
    }
}
