<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Database\Seeder;

class HotelSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::where('email', 'owner@example.com')->first();

        if (!$owner) {
            return;
        }

        $hotel = Hotel::updateOrCreate(
            ['owner_id' => $owner->id, 'name' => 'Khách sạn Phenikaa Hà Nội'],
            [
                'description' => 'Khách sạn mẫu phục vụ demo hệ thống đặt phòng trực tuyến.',
                'province' => 'Hà Nội',
                'district' => 'Hà Đông',
                'ward' => 'Yên Nghĩa',
                'address' => 'Đường Nguyễn Trác, Hà Đông, Hà Nội',
                'checkin_time' => '14:00:00',
                'checkout_time' => '12:00:00',
                'thumbnail' => null,
                'status' => 'active',
                'average_rating' => 0,
                'review_count' => 0,
            ]
        );

        $amenities = Amenity::where('type', 'hotel')
            ->whereIn('name', [
                'Wi-Fi miễn phí',
                'Bãi đỗ xe',
                'Lễ tân 24/7',
                'Thang máy',
            ])
            ->pluck('id')
            ->toArray();

        $hotel->amenities()->sync($amenities);
    }
}