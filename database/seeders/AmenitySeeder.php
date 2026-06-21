<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;

class AmenitySeeder extends Seeder
{
    public function run(): void
    {
        $amenities = [
            ['name' => 'Wi-Fi miễn phí', 'icon' => 'wifi', 'type' => 'hotel', 'status' => 'active'],
            ['name' => 'Wifi miễn phí', 'icon' => 'wifi', 'type' => 'hotel', 'status' => 'active'],
            ['name' => 'Bãi đỗ xe', 'icon' => 'parking', 'type' => 'hotel', 'status' => 'active'],
            ['name' => 'Hồ bơi', 'icon' => 'pool', 'type' => 'hotel', 'status' => 'active'],
            ['name' => 'Nhà hàng', 'icon' => 'restaurant', 'type' => 'hotel', 'status' => 'active'],
            ['name' => 'Lễ tân 24/7', 'icon' => 'reception', 'type' => 'hotel', 'status' => 'active'],
            ['name' => 'Thang máy', 'icon' => 'elevator', 'type' => 'hotel', 'status' => 'active'],
            ['name' => 'Dịch vụ giặt là', 'icon' => 'laundry', 'type' => 'hotel', 'status' => 'active'],
            ['name' => 'Đưa đón sân bay', 'icon' => 'airport', 'type' => 'hotel', 'status' => 'active'],

            ['name' => 'Điều hòa', 'icon' => 'ac', 'type' => 'room_type', 'status' => 'active'],
            ['name' => 'TV màn hình phẳng', 'icon' => 'tv', 'type' => 'room_type', 'status' => 'active'],
            ['name' => 'Phòng tắm riêng', 'icon' => 'bath', 'type' => 'room_type', 'status' => 'active'],
            ['name' => 'Ban công', 'icon' => 'balcony', 'type' => 'room_type', 'status' => 'active'],
            ['name' => 'Bồn tắm', 'icon' => 'bathtub', 'type' => 'room_type', 'status' => 'active'],
        ];

        foreach ($amenities as $amenity) {
            Amenity::updateOrCreate(
                ['name' => $amenity['name']],
                $amenity
            );
        }
    }
}
