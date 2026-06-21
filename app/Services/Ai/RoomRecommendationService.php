<?php

namespace App\Services\Ai;

use App\Models\RoomType;
use App\Services\Booking\AvailabilityService;
use Illuminate\Support\Collection;

class RoomRecommendationService
{
    public function __construct(private AvailabilityService $availabilityService)
    {
    }

    public function recommend(RoomType $source, ?string $checkinDate = null, ?string $checkoutDate = null, int $guests = 1, int $limit = 6): Collection
    {
        $source->loadMissing(['hotel', 'amenities']);
        $sourceAmenityIds = $source->amenities->pluck('id')->all();
        $sourcePrice = max(1, (float) $source->price_per_night);
        $checkinDate = $checkinDate ?: now()->toDateString();
        $checkoutDate = $checkoutDate ?: now()->addDay()->toDateString();

        return RoomType::with(['hotel', 'amenities', 'rooms'])
            ->where('status', 'active')
            ->where('id', '!=', $source->id)
            ->where('max_guests', '>=', max(1, $guests))
            ->whereHas('hotel', fn ($query) => $query->where('status', 'active'))
            ->get()
            ->map(function (RoomType $candidate) use ($source, $sourceAmenityIds, $sourcePrice, $checkinDate, $checkoutDate) {
                $availableCount = $this->availabilityService->availableRoomCount($candidate, $checkinDate, $checkoutDate);

                if ($availableCount <= 0) {
                    return null;
                }

                $candidateAmenityIds = $candidate->amenities->pluck('id')->all();
                $intersection = count(array_intersect($sourceAmenityIds, $candidateAmenityIds));
                $union = max(1, count(array_unique(array_merge($sourceAmenityIds, $candidateAmenityIds))));
                $amenityScore = ($intersection / $union) * 40;

                $candidatePrice = max(1, (float) $candidate->price_per_night);
                $priceScore = max(0, 1 - (abs($candidatePrice - $sourcePrice) / max($candidatePrice, $sourcePrice))) * 25;

                $capacityScore = max(0, 1 - (abs((int) $candidate->max_guests - (int) $source->max_guests) / max((int) $candidate->max_guests, (int) $source->max_guests, 1))) * 15;

                $locationScore = 0;
                if ($candidate->hotel->district && $candidate->hotel->district === $source->hotel->district) {
                    $locationScore = 10;
                } elseif ($candidate->hotel->province && $candidate->hotel->province === $source->hotel->province) {
                    $locationScore = 7;
                }

                $ratingScore = min(10, ((float) $candidate->hotel->average_rating / 5) * 10);
                $score = round($amenityScore + $priceScore + $capacityScore + $locationScore + $ratingScore, 2);

                return [
                    'room_type' => $candidate,
                    'score' => $score,
                    'available_count' => $availableCount,
                    'reason' => $this->reason($candidate, $source, $intersection, $priceScore, $locationScore),
                ];
            })
            ->filter()
            ->sortByDesc('score')
            ->take($limit)
            ->values();
    }

    private function reason(RoomType $candidate, RoomType $source, int $sharedAmenities, float $priceScore, float $locationScore): string
    {
        if ($sharedAmenities >= 3) {
            return "Có {$sharedAmenities} tiện nghi giống hạng phòng đang xem.";
        }

        if ($locationScore >= 10) {
            return 'Cùng khu vực khách sạn nên thuận tiện so sánh.';
        }

        if ($priceScore >= 18) {
            return 'Mức giá gần với hạng phòng đang xem.';
        }

        if ((int) $candidate->max_guests === (int) $source->max_guests) {
            return 'Sức chứa tương đương, phù hợp nhóm khách tương tự.';
        }

        return 'Được gợi ý dựa trên giá, tiện nghi, sức chứa và đánh giá khách sạn.';
    }
}
