<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingRoomAssignment;
use App\Models\BookingRoomType;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class R46DemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->clearOldDemoData();

            $owner = $this->createUser([
                'name' => 'Owner Demo R4.6',
                'email' => 'owner46@test.com',
                'password' => Hash::make('12345678'),
                'role' => 'owner',
                'status' => 'active',
                'phone' => '0900000046',
            ]);

            $customer = $this->createUser([
                'name' => 'Customer Demo R4.6',
                'email' => 'customer46@test.com',
                'password' => Hash::make('12345678'),
                'role' => 'customer',
                'status' => 'active',
                'phone' => '0910000046',
            ]);

            $hotel = $this->createHotel([
                'owner_id' => $owner->id,
                'name' => 'Khách sạn Demo R4.6',
                'description' => 'Khách sạn dùng để demo chức năng điều phối lưu trú R4.6.',
                'cancellation_policy' => 'Dữ liệu demo. Chính sách hủy/hoàn tiền dùng để trình bày.',
                'province' => 'Hà Nội',
                'district' => 'Hà Đông',
                'ward' => 'Yên Nghĩa',
                'address' => 'Demo R4.6 - Phenikaa',
                'checkin_time' => '14:00',
                'checkout_time' => '12:00',
                'status' => 'active',
            ]);

            $standard = $this->createRoomType([
                'hotel_id' => $hotel->id,
                'name' => 'Standard Demo R4.6',
                'description' => 'Hạng phòng dùng để test check-in, đổi phòng, check-out.',
                'max_guests' => 2,
                'bed_type' => '1 giường đôi',
                'area' => 25,
                'price_per_night' => 500000,
                'status' => 'active',
            ]);

            $maintenanceType = $this->createRoomType([
                'hotel_id' => $hotel->id,
                'name' => 'Maintenance Demo R4.6',
                'description' => 'Hạng phòng dùng để test thiếu phòng sẵn sàng.',
                'max_guests' => 2,
                'bed_type' => '1 giường đôi',
                'area' => 25,
                'price_per_night' => 450000,
                'status' => 'active',
            ]);

            /*
             * Nhóm phòng Standard:
             * - 101, 102, 103: dùng check-in và đổi phòng.
             */
            $room101 = $this->createRoom([
                'hotel_id' => $hotel->id,
                'room_type_id' => $standard->id,
                'room_number' => 'R46-101',
                'floor' => '1',
                'status' => 'available',
                'note' => 'Phòng demo check-in hôm nay.',
            ]);

            $room102 = $this->createRoom([
                'hotel_id' => $hotel->id,
                'room_type_id' => $standard->id,
                'room_number' => 'R46-102',
                'floor' => '1',
                'status' => 'available',
                'note' => 'Phòng demo đổi phòng.',
            ]);

            $room103 = $this->createRoom([
                'hotel_id' => $hotel->id,
                'room_type_id' => $standard->id,
                'room_number' => 'R46-103',
                'floor' => '1',
                'status' => 'available',
                'note' => 'Phòng demo phụ.',
            ]);

            /*
             * Nhóm phòng Maintenance:
             * - Không có phòng available để test manual_review.
             */
            $this->createRoom([
                'hotel_id' => $hotel->id,
                'room_type_id' => $maintenanceType->id,
                'room_number' => 'R46-201',
                'floor' => '2',
                'status' => 'maintenance',
                'note' => 'Phòng đang bảo trì để test thiếu phòng sẵn sàng.',
            ]);

            $this->createRoom([
                'hotel_id' => $hotel->id,
                'room_type_id' => $maintenanceType->id,
                'room_number' => 'R46-202',
                'floor' => '2',
                'status' => 'maintenance',
                'note' => 'Phòng đang bảo trì để test thiếu phòng sẵn sàng.',
            ]);

            /*
             * Booking 1:
             * Check-in hôm nay => Owner bấm Check-in được.
             */
            $this->createBookingWithRoomType([
                'customer_id' => $customer->id,
                'hotel_id' => $hotel->id,
                'booking_code' => 'R46DEMO-TODAY',
                'checkin_date' => now()->toDateString(),
                'checkout_date' => now()->addDay()->toDateString(),
                'guest_count' => 2,
                'contact_name' => 'Khách Demo Check-in Hôm Nay',
                'contact_phone' => '0911111111',
                'contact_email' => 'customer46@test.com',
                'special_request' => 'Demo check-in trong cùng ngày.',
                'total_amount' => 500000,
                'status' => 'confirmed',
                'hold_expires_at' => null,
            ], $standard, 1);

            /*
             * Booking 2:
             * Check-in ngày mai => bấm Check-in bị chặn.
             */
            $this->createBookingWithRoomType([
                'customer_id' => $customer->id,
                'hotel_id' => $hotel->id,
                'booking_code' => 'R46DEMO-TOMORROW',
                'checkin_date' => now()->addDay()->toDateString(),
                'checkout_date' => now()->addDays(2)->toDateString(),
                'guest_count' => 2,
                'contact_name' => 'Khách Demo Check-in Sớm',
                'contact_phone' => '0922222222',
                'contact_email' => 'customer46@test.com',
                'special_request' => 'Demo chặn check-in trước ngày nhận phòng.',
                'total_amount' => 500000,
                'status' => 'confirmed',
                'hold_expires_at' => null,
            ], $standard, 1);

            /*
             * Booking 3:
             * Hôm nay nhưng hạng phòng không có phòng available => chuyển manual_review khi bấm Check-in.
             */
            $this->createBookingWithRoomType([
                'customer_id' => $customer->id,
                'hotel_id' => $hotel->id,
                'booking_code' => 'R46DEMO-MANUAL',
                'checkin_date' => now()->toDateString(),
                'checkout_date' => now()->addDay()->toDateString(),
                'guest_count' => 2,
                'contact_name' => 'Khách Demo Thiếu Phòng',
                'contact_phone' => '0933333333',
                'contact_email' => 'customer46@test.com',
                'special_request' => 'Demo không đủ phòng sẵn sàng.',
                'total_amount' => 450000,
                'status' => 'confirmed',
                'hold_expires_at' => null,
            ], $maintenanceType, 1);

            /*
             * Booking 4:
             * Đã check-in sẵn => dùng để test đổi phòng và check-out.
             * R46-101 occupied, R46-102/R46-103 available.
             */
            $stayingBooking = $this->createBookingWithRoomType([
                'customer_id' => $customer->id,
                'hotel_id' => $hotel->id,
                'booking_code' => 'R46DEMO-STAYING',
                'checkin_date' => now()->toDateString(),
                'checkout_date' => now()->addDay()->toDateString(),
                'guest_count' => 2,
                'contact_name' => 'Khách Demo Đang Lưu Trú',
                'contact_phone' => '0944444444',
                'contact_email' => 'customer46@test.com',
                'special_request' => 'Demo đổi phòng và check-out.',
                'total_amount' => 500000,
                'status' => 'staying',
                'hold_expires_at' => null,
                'checked_in_at' => now(),
                'checkin_note' => 'Seeder tạo sẵn trạng thái đang lưu trú để demo đổi phòng/check-out.',
            ], $standard, 1);

            BookingRoomAssignment::create([
                'booking_id' => $stayingBooking->id,
                'room_id' => $room101->id,
                'assigned_at' => now(),
                'released_at' => null,
                'note' => 'Gán phòng sẵn từ seeder để demo đổi phòng/check-out.',
            ]);

            $room101->update([
                'status' => 'occupied',
            ]);

            $room102->update([
                'status' => 'available',
            ]);

            $room103->update([
                'status' => 'available',
            ]);
        });
    }

    private function clearOldDemoData(): void
    {
        $bookingIds = Booking::where('booking_code', 'like', 'R46DEMO-%')->pluck('id');

        if ($bookingIds->isNotEmpty()) {
            if (Schema::hasTable('booking_room_assignments')) {
                BookingRoomAssignment::whereIn('booking_id', $bookingIds)->delete();
            }

            if (Schema::hasTable('booking_room_types')) {
                BookingRoomType::whereIn('booking_id', $bookingIds)->delete();
            }

            if (Schema::hasTable('payments')) {
                DB::table('payments')->whereIn('booking_id', $bookingIds)->delete();
            }

            if (Schema::hasTable('financial_transactions')) {
                DB::table('financial_transactions')->whereIn('booking_id', $bookingIds)->delete();
            }

            Booking::whereIn('id', $bookingIds)->delete();
        }

        if (Schema::hasTable('rooms')) {
            Room::where('room_number', 'like', 'R46-%')->delete();
        }

        if (Schema::hasTable('room_types')) {
            RoomType::where('name', 'like', '%Demo R4.6%')->delete();
        }

        if (Schema::hasTable('hotels')) {
            Hotel::where('name', 'Khách sạn Demo R4.6')->delete();
        }
    }

    private function createUser(array $attributes): User
    {
        $data = $this->filterColumns('users', $attributes);

        return User::updateOrCreate(
            ['email' => $attributes['email']],
            $data
        );
    }

    private function createHotel(array $attributes): Hotel
    {
        return Hotel::create($this->filterColumns('hotels', $attributes));
    }

    private function createRoomType(array $attributes): RoomType
    {
        return RoomType::create($this->filterColumns('room_types', $attributes));
    }

    private function createRoom(array $attributes): Room
    {
        return Room::create($this->filterColumns('rooms', $attributes));
    }

    private function createBookingWithRoomType(array $bookingAttributes, RoomType $roomType, int $quantity): Booking
    {
        $booking = Booking::create($this->filterColumns('bookings', $bookingAttributes));

        $checkin = Carbon::parse($bookingAttributes['checkin_date']);
        $checkout = Carbon::parse($bookingAttributes['checkout_date']);
        $nights = max(1, $checkin->diffInDays($checkout));

        BookingRoomType::create($this->filterColumns('booking_room_types', [
            'booking_id' => $booking->id,
            'room_type_id' => $roomType->id,
            'quantity' => $quantity,
            'price_per_night' => $roomType->price_per_night,
            'nights' => $nights,
            'subtotal' => $roomType->price_per_night * $quantity * $nights,
        ]));

        return $booking;
    }

    private function filterColumns(string $table, array $attributes): array
    {
        return collect($attributes)
            ->filter(fn ($value, $key) => Schema::hasColumn($table, $key))
            ->toArray();
    }
}