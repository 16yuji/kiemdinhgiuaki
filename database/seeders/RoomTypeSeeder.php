<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\Hotel;
use App\Models\RoomType;
use Illuminate\Database\Seeder;

class RoomTypeSeeder extends Seeder
{
    public function run(): void
    {
        $hotel = Hotel::where('name', 'Khách sạn Phenikaa Hà Nội')->first();

        if (!$hotel) {
            return;
        }

        $standard = RoomType::updateOrCreate(
            ['hotel_id' => $hotel->id, 'name' => 'Standard'],
            [
                'description' => 'Phòng tiêu chuẩn, phù hợp cho 1-2 khách.',
                'max_guests' => 2,
                'bed_type' => '1 giường đôi',
                'area' => 22,
                'price_per_night' => 500000,
                'thumbnail' => null,
                'status' => 'active',
            ]
        );

        $deluxe = RoomType::updateOrCreate(
            ['hotel_id' => $hotel->id, 'name' => 'Deluxe'],
            [
                'description' => 'Phòng rộng hơn, tiện nghi tốt hơn, phù hợp cho nghỉ dưỡng.',
                'max_guests' => 3,
                'bed_type' => '1 giường đôi lớn',
                'area' => 32,
                'price_per_night' => 800000,
                'thumbnail' => null,
                'status' => 'active',
            ]
        );

        $roomAmenityIds = Amenity::where('type', 'room_type')
            ->whereIn('name', [
                'Điều hòa',
                'TV',
                'Phòng tắm riêng',
                'Bàn làm việc',
            ])
            ->pluck('id')
            ->toArray();

        $standard->amenities()->sync($roomAmenityIds);
        $deluxe->amenities()->sync($roomAmenityIds);
    }
}