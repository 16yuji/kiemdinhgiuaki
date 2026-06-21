<?php

namespace Database\Seeders;

use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $hotel = Hotel::where('name', 'Khách sạn Phenikaa Hà Nội')->first();

        if (!$hotel) {
            return;
        }

        $standard = RoomType::where('hotel_id', $hotel->id)
            ->where('name', 'Standard')
            ->first();

        $deluxe = RoomType::where('hotel_id', $hotel->id)
            ->where('name', 'Deluxe')
            ->first();

        if ($standard) {
            $standardRooms = [
                ['room_number' => '101', 'floor' => '1'],
                ['room_number' => '102', 'floor' => '1'],
            ];

            foreach ($standardRooms as $room) {
                Room::updateOrCreate(
                    [
                        'hotel_id' => $hotel->id,
                        'room_number' => $room['room_number'],
                    ],
                    [
                        'room_type_id' => $standard->id,
                        'floor' => $room['floor'],
                        'status' => 'available',
                        'note' => null,
                    ]
                );
            }
        }

        if ($deluxe) {
            $deluxeRooms = [
                ['room_number' => '201', 'floor' => '2'],
                ['room_number' => '202', 'floor' => '2'],
            ];

            foreach ($deluxeRooms as $room) {
                Room::updateOrCreate(
                    [
                        'hotel_id' => $hotel->id,
                        'room_number' => $room['room_number'],
                    ],
                    [
                        'room_type_id' => $deluxe->id,
                        'floor' => $room['floor'],
                        'status' => 'available',
                        'note' => null,
                    ]
                );
            }
        }
    }
}