<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Models\RoomType;
use App\Services\Ai\RoomRecommendationService;
use Illuminate\Http\Request;

class RoomRecommendationController extends Controller
{
    public function index(Request $request, RoomType $roomType, RoomRecommendationService $recommendationService)
    {
        $data = $request->validate([
            'checkin_date' => ['nullable', 'date', 'after_or_equal:today'],
            'checkout_date' => ['nullable', 'date', 'after:checkin_date'],
            'guests' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($roomType->status !== 'active' || $roomType->hotel->status !== 'active') {
            abort(404);
        }

        $items = $recommendationService
            ->recommend(
                $roomType,
                $data['checkin_date'] ?? now()->toDateString(),
                $data['checkout_date'] ?? now()->addDay()->toDateString(),
                (int) ($data['guests'] ?? 1)
            )
            ->map(function (array $item) use ($data) {
                $recommended = $item['room_type'];
                $hotel = $recommended->hotel;

                return [
                    'hotel_name' => $hotel->name,
                    'room_type_name' => $recommended->name,
                    'location' => collect([$hotel->district, $hotel->province])->filter()->implode(', '),
                    'price' => (float) $recommended->price_per_night,
                    'rating' => (float) $hotel->average_rating,
                    'available_count' => $item['available_count'],
                    'score' => $item['score'],
                    'reason' => $item['reason'],
                    'thumbnail_url' => $recommended->thumbnail ? '/storage/' . ltrim($recommended->thumbnail, '/') : null,
                    'url' => route('customer.hotels.show', [
                        'hotel' => $hotel,
                        'checkin_date' => $data['checkin_date'] ?? now()->toDateString(),
                        'checkout_date' => $data['checkout_date'] ?? now()->addDay()->toDateString(),
                        'guests' => $data['guests'] ?? 1,
                    ], false),
                ];
            })
            ->values();

        return response()->json([
            'source' => [
                'name' => $roomType->name,
                'hotel_name' => $roomType->hotel->name,
            ],
            'items' => $items,
        ]);
    }
}
