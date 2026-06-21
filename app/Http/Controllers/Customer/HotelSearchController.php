<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\Hotel;
use App\Services\Booking\AvailabilityService;
use Illuminate\Http\Request;

class HotelSearchController extends Controller
{
    public function index(Request $request, AvailabilityService $availabilityService)
    {
        $data = $request->validate([
            'location' => ['nullable', 'string', 'max:255'],
            'checkin_date' => ['nullable', 'date', 'after_or_equal:today'],
            'checkout_date' => ['nullable', 'date', 'after:checkin_date'],
            'guests' => ['nullable', 'integer', 'min:1'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'gte:min_price'],
            'amenity_ids' => ['nullable', 'array'],
            'amenity_ids.*' => ['exists:amenities,id'],
            'min_rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
        ], [
            'checkin_date.after_or_equal' => 'Ngày nhận phòng không được nhỏ hơn ngày hiện tại.',
            'checkout_date.after' => 'Ngày trả phòng phải sau ngày nhận phòng.',
            'max_price.gte' => 'Giá tối đa phải lớn hơn hoặc bằng giá tối thiểu.',
            'amenity_ids.*.exists' => 'Tiện nghi được chọn không hợp lệ.',
            'min_rating.max' => 'Điểm đánh giá không hợp lệ.',
        ]);

        $location = $data['location'] ?? null;
        $checkinDate = $data['checkin_date'] ?? now()->toDateString();
        $checkoutDate = $data['checkout_date'] ?? now()->addDay()->toDateString();
        $guests = (int) ($data['guests'] ?? 1);
        $amenityIds = $data['amenity_ids'] ?? [];
        $minRating = $data['min_rating'] ?? null;

        $hotelAmenities = Amenity::where('status', 'active')
            ->where(function ($query) {
                $query->where('type', 'hotel')
                    ->orWhereNull('type');
            })
            ->orderBy('name')
            ->get();

        $hotels = Hotel::with(['roomTypes.rooms', 'reviews', 'amenities'])
            ->where('status', 'active')
            ->when($location, function ($query) use ($location) {
                $query->where(function ($subQuery) use ($location) {
                    $subQuery->where('province', 'like', "%{$location}%")
                        ->orWhere('district', 'like', "%{$location}%")
                        ->orWhere('ward', 'like', "%{$location}%")
                        ->orWhere('address', 'like', "%{$location}%")
                        ->orWhere('name', 'like', "%{$location}%");
                });
            })
            ->when($minRating !== null && $minRating !== '', function ($query) use ($minRating) {
                $query->where('average_rating', '>=', $minRating);
            })
            ->when(!empty($amenityIds), function ($query) use ($amenityIds) {
                foreach ($amenityIds as $amenityId) {
                    $query->whereHas('amenities', function ($amenityQuery) use ($amenityId) {
                        $amenityQuery->where('amenities.id', $amenityId);
                    });
                }
            })
            ->whereHas('roomTypes', function ($query) use ($guests, $data) {
                $query->where('status', 'active')
                    ->where('max_guests', '>=', $guests)
                    ->when($data['min_price'] ?? null, function ($q, $minPrice) {
                        $q->where('price_per_night', '>=', $minPrice);
                    })
                    ->when($data['max_price'] ?? null, function ($q, $maxPrice) {
                        $q->where('price_per_night', '<=', $maxPrice);
                    });
            })
            ->latest()
            ->paginate(9)
            ->withQueryString();

        $hotels->getCollection()->transform(function ($hotel) use ($availabilityService, $checkinDate, $checkoutDate, $guests, $data) {
            $availableRoomTypes = $hotel->roomTypes
                ->where('status', 'active')
                ->filter(function ($roomType) use ($availabilityService, $checkinDate, $checkoutDate, $guests, $data) {
                    if ($roomType->max_guests < $guests) {
                        return false;
                    }

                    if (!empty($data['min_price']) && $roomType->price_per_night < $data['min_price']) {
                        return false;
                    }

                    if (!empty($data['max_price']) && $roomType->price_per_night > $data['max_price']) {
                        return false;
                    }

                    return $availabilityService->availableRoomCount(
                        $roomType,
                        $checkinDate,
                        $checkoutDate
                    ) > 0;
                });

            $hotel->available_room_types_count = $availableRoomTypes->count();
            $hotel->min_price = $availableRoomTypes->min('price_per_night');

            return $hotel;
        });

        $hotels->setCollection(
            $hotels->getCollection()
                ->filter(fn ($hotel) => $hotel->available_room_types_count > 0)
                ->values()
        );

        return view('customer.hotels.index', [
            'hotels' => $hotels,
            'hotelAmenities' => $hotelAmenities,
            'location' => $location,
            'checkinDate' => $checkinDate,
            'checkoutDate' => $checkoutDate,
            'guests' => $guests,
            'minPrice' => $data['min_price'] ?? null,
            'maxPrice' => $data['max_price'] ?? null,
            'selectedAmenityIds' => $amenityIds,
            'minRating' => $minRating,
        ]);
    }
}
