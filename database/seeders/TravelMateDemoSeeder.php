<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\Booking;
use App\Models\BookingRoomAssignment;
use App\Models\BookingRoomType;
use App\Models\FinancialTransaction;
use App\Models\Hotel;
use App\Models\HotelImage;
use App\Models\OwnerAdjustment;
use App\Models\PartnerRequest;
use App\Models\Payment;
use App\Models\Review;
use App\Models\ReviewReply;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\RoomTypeImage;
use App\Models\Settlement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TravelMateDemoSeeder extends Seeder
{
    private array $hotelAmenities = [];

    private array $roomAmenities = [];

    private array $hotelPhotoUrls = [
        'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1445019980597-93fa8acb246c?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1564501049412-61c2a3083791?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1584132967334-10e028bd69f7?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1596394516093-501ba68a0ba6?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1582719508461-905c673771fd?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1561501900-3701fa6a0864?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1501117716987-c8e1ecb2109a?auto=format&fit=crop&w=1200&q=80',
    ];

    private array $roomPhotoUrls = [
        'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1598928506311-c55ded91a20c?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1560185127-6ed189bf02f4?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?auto=format&fit=crop&w=1200&q=80',
    ];

    public function run(): void
    {
        DB::transaction(function () {
            $this->clearDemoData();
            $this->ensureStorageLinkHint();
            $this->seedAmenities();

            $admin = $this->user('admin.demo@travelmate.test', [
                'name' => 'Travel Mate Admin Demo',
                'role' => 'admin',
                'phone' => '19009999',
            ]);

            $owners = [
                $this->user('owner.north@travelmate.test', [
                    'name' => 'Owner North Collection',
                    'role' => 'owner',
                    'phone' => '0901000001',
                    'bank_name' => 'Vietcombank',
                    'bank_account_number' => '1010101010',
                    'bank_account_name' => 'OWNER NORTH COLLECTION',
                ]),
                $this->user('owner.coast@travelmate.test', [
                    'name' => 'Owner Coast Collection',
                    'role' => 'owner',
                    'phone' => '0901000002',
                    'bank_name' => 'ACB',
                    'bank_account_number' => '2020202020',
                    'bank_account_name' => 'OWNER COAST COLLECTION',
                ]),
                $this->user('owner.city@travelmate.test', [
                    'name' => 'Owner City Collection',
                    'role' => 'owner',
                    'phone' => '0901000003',
                    'bank_name' => 'Techcombank',
                    'bank_account_number' => '3030303030',
                    'bank_account_name' => 'OWNER CITY COLLECTION',
                ]),
            ];

            $customers = [
                $this->user('customer.mai@travelmate.test', ['name' => 'Mai Nguyen', 'role' => 'customer', 'phone' => '0912000001']),
                $this->user('customer.long@travelmate.test', ['name' => 'Long Tran', 'role' => 'customer', 'phone' => '0912000002']),
                $this->user('customer.linh@travelmate.test', ['name' => 'Linh Pham', 'role' => 'customer', 'phone' => '0912000003']),
                $this->user('customer.an@travelmate.test', ['name' => 'An Hoang', 'role' => 'customer', 'phone' => '0912000004']),
                $this->user('customer.partner@travelmate.test', ['name' => 'Partner Request Demo', 'role' => 'customer', 'phone' => '0912000005']),
            ];

            $this->seedPartnerRequests($customers[4], $admin);

            $hotels = $this->seedHotels($owners);
            $this->seedBookings($hotels, $customers, $admin);
            $this->refreshHotelRatings();
        });
    }

    private function clearDemoData(): void
    {
        $bookingIds = Booking::where('booking_code', 'like', 'TMDEMO-%')->pluck('id');
        $financialIds = FinancialTransaction::whereIn('booking_id', $bookingIds)->pluck('id');
        $reviewIds = Review::whereIn('booking_id', $bookingIds)->pluck('id');

        ReviewReply::whereIn('review_id', $reviewIds)->delete();
        Review::whereIn('id', $reviewIds)->delete();
        OwnerAdjustment::whereIn('booking_id', $bookingIds)->orWhereIn('financial_transaction_id', $financialIds)->delete();
        Settlement::whereIn('financial_transaction_id', $financialIds)->delete();
        FinancialTransaction::whereIn('id', $financialIds)->delete();
        Payment::whereIn('booking_id', $bookingIds)->delete();
        BookingRoomAssignment::whereIn('booking_id', $bookingIds)->delete();
        BookingRoomType::whereIn('booking_id', $bookingIds)->delete();
        Booking::whereIn('id', $bookingIds)->delete();

        $hotelIds = Hotel::where('name', 'like', 'Travel Mate Demo - %')->pluck('id');
        $roomTypeIds = RoomType::whereIn('hotel_id', $hotelIds)->pluck('id');

        RoomTypeImage::whereIn('room_type_id', $roomTypeIds)->delete();
        Room::whereIn('hotel_id', $hotelIds)->delete();
        RoomType::whereIn('id', $roomTypeIds)->delete();
        HotelImage::whereIn('hotel_id', $hotelIds)->delete();
        DB::table('hotel_amenity')->whereIn('hotel_id', $hotelIds)->delete();
        Hotel::whereIn('id', $hotelIds)->delete();

        PartnerRequest::where('business_name', 'like', 'TM Demo Partner%')->delete();
    }

    private function ensureStorageLinkHint(): void
    {
        Storage::disk('public')->makeDirectory('demo/hotels');
        Storage::disk('public')->makeDirectory('demo/rooms');
        Storage::disk('public')->makeDirectory('demo/avatars');
    }

    private function seedAmenities(): void
    {
        $hotelItems = [
            ['Free Wi-Fi', 'wifi'],
            ['Ho boi', 'pool'],
            ['Nha hang', 'restaurant'],
            ['Le tan 24/7', 'reception'],
            ['Bai do xe', 'parking'],
            ['Spa', 'spa'],
            ['Phong gym', 'gym'],
            ['Dua don san bay', 'airport'],
            ['Gan bien', 'beach'],
            ['View nui', 'mountain'],
        ];

        $roomItems = [
            ['Dieu hoa', 'ac'],
            ['TV man hinh phang', 'tv'],
            ['Phong tam rieng', 'bath'],
            ['Ban cong', 'balcony'],
            ['Bon tam', 'bathtub'],
            ['Ban lam viec', 'desk'],
            ['Mini bar', 'minibar'],
            ['May say toc', 'dryer'],
        ];

        foreach ($hotelItems as [$name, $icon]) {
            Amenity::updateOrCreate(['name' => $name], ['type' => 'hotel', 'icon' => $icon, 'status' => 'active']);
        }

        foreach ($roomItems as [$name, $icon]) {
            Amenity::updateOrCreate(['name' => $name], ['type' => 'room_type', 'icon' => $icon, 'status' => 'active']);
        }

        $this->hotelAmenities = Amenity::where('type', 'hotel')->where('status', 'active')->pluck('id')->all();
        $this->roomAmenities = Amenity::where('type', 'room_type')->where('status', 'active')->pluck('id')->all();
    }

    private function user(string $email, array $data): User
    {
        return User::updateOrCreate(
            ['email' => $email],
            array_merge([
                'password' => Hash::make('12345678'),
                'status' => 'active',
                'email_verified_at' => now(),
            ], $data)
        );
    }

    private function seedPartnerRequests(User $customer, User $admin): void
    {
        PartnerRequest::create([
            'user_id' => $customer->id,
            'business_name' => 'TM Demo Partner - Green Hill Homestay',
            'address' => 'Da Lat, Lam Dong',
            'contact_phone' => '0912999000',
            'contact_email' => 'partner.demo@travelmate.test',
            'description' => 'Ho so demo dang cho Admin duyet de nang cap tai khoan thanh Owner.',
            'status' => 'pending',
        ]);

        PartnerRequest::create([
            'user_id' => $customer->id,
            'business_name' => 'TM Demo Partner - Old Request',
            'address' => 'Hoi An, Quang Nam',
            'contact_phone' => '0912999001',
            'contact_email' => 'partner.old@travelmate.test',
            'description' => 'Ho so demo da bi tu choi.',
            'status' => 'rejected',
            'reject_reason' => 'Thieu giay to xac minh co so luu tru.',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now()->subDays(8),
        ]);
    }

    private function seedHotels(array $owners): array
    {
        $items = [
            ['Sapa Cloud Retreat', 0, 'Sa Pa', 'Lao Cai', 'Sa Pa', 'Phuong Sa Pa', '31 Xuan Vien', 22.3364, 103.8438, 4.7, 38, ['View nui', 'Spa', 'Free Wi-Fi', 'Le tan 24/7']],
            ['Hanoi Lakeside Boutique', 0, 'Ha Noi', 'Ha Noi', 'Hoan Kiem', 'Phuong Hang Trong', '12 Bao Khanh', 21.0304, 105.8502, 4.5, 24, ['Free Wi-Fi', 'Nha hang', 'Dua don san bay', 'Le tan 24/7']],
            ['Da Nang Ocean Pearl', 1, 'Da Nang', 'Da Nang', 'Son Tra', 'Phuong Phuoc My', '88 Vo Nguyen Giap', 16.0678, 108.2455, 4.8, 52, ['Gan bien', 'Ho boi', 'Spa', 'Phong gym']],
            ['Hoi An Lantern Resort', 1, 'Hoi An', 'Quang Nam', 'Hoi An', 'Phuong Cam Pho', '45 Nguyen Phuc Tan', 15.8765, 108.3267, 4.6, 31, ['Ho boi', 'Nha hang', 'Free Wi-Fi', 'Bai do xe']],
            ['Nha Trang Bay View', 1, 'Nha Trang', 'Khanh Hoa', 'Nha Trang', 'Phuong Loc Tho', '26 Tran Phu', 12.2388, 109.1967, 4.4, 28, ['Gan bien', 'Ho boi', 'Nha hang', 'Le tan 24/7']],
            ['Da Lat Pine Garden', 2, 'Da Lat', 'Lam Dong', 'Da Lat', 'Phuong 3', '7 Trieu Viet Vuong', 11.9304, 108.4419, 4.3, 19, ['View nui', 'Bai do xe', 'Free Wi-Fi', 'Nha hang']],
            ['Saigon Central Suites', 2, 'TP Ho Chi Minh', 'TP Ho Chi Minh', 'Quan 1', 'Phuong Ben Nghe', '19 Dong Khoi', 10.7769, 106.7031, 4.5, 36, ['Phong gym', 'Le tan 24/7', 'Dua don san bay', 'Free Wi-Fi']],
            ['Phu Quoc Sunset Bay', 2, 'Phu Quoc', 'Kien Giang', 'Phu Quoc', 'Phuong Duong Dong', '110 Tran Hung Dao', 10.2165, 103.9590, 4.7, 44, ['Gan bien', 'Ho boi', 'Spa', 'Nha hang']],
        ];

        $hotels = [];

        foreach ($items as $index => [$shortName, $ownerIndex, $city, $province, $district, $ward, $address, $lat, $lng, $rating, $count, $amenityNames]) {
            $hotel = Hotel::create([
                'owner_id' => $owners[$ownerIndex]->id,
                'name' => 'Travel Mate Demo - ' . $shortName,
                'description' => 'Khach san demo phong cach premium, du lieu du de test tim kiem, dat phong, danh gia va van hanh luu tru.',
                'province' => $province,
                'district' => $district,
                'ward' => $ward,
                'address' => $address,
                'latitude' => $lat,
                'longitude' => $lng,
                'checkin_time' => '14:00:00',
                'checkout_time' => '12:00:00',
                'thumbnail' => $this->image("demo/hotels/hotel-{$index}-hero.svg", $shortName, $city, $index),
                'status' => 'active',
                'average_rating' => $rating,
                'review_count' => $count,
                'cancellation_policy' => 'Huy truoc ngay nhan phong co the duoc Admin Travel Mate xem xet hoan tien theo tinh trang thanh toan va chinh sach cua khach san. Trong ngay nhan phong, khach can lien he ho tro.',
            ]);

            $hotel->amenities()->sync(Amenity::whereIn('name', $amenityNames)->pluck('id')->all());

            for ($i = 1; $i <= 3; $i++) {
                HotelImage::create([
                    'hotel_id' => $hotel->id,
                    'image_path' => $this->image("demo/hotels/hotel-{$index}-gallery-{$i}.svg", $shortName, "Gallery {$i}", $index + $i),
                    'is_thumbnail' => false,
                    'sort_order' => $i,
                ]);
            }

            $this->seedRoomTypes($hotel, $index);
            $hotels[] = $hotel->load('roomTypes.rooms');
        }

        return $hotels;
    }

    private function seedRoomTypes(Hotel $hotel, int $hotelIndex): void
    {
        $types = [
            ['Standard Comfort', 2, '1 queen bed', 24, 550000],
            ['Deluxe View', 3, '1 king bed', 34, 880000],
            ['Family Suite', 4, '2 double beds', 48, 1350000],
        ];

        foreach ($types as $typeIndex => [$name, $guests, $bed, $area, $price]) {
            $roomType = RoomType::create([
                'hotel_id' => $hotel->id,
                'name' => $name,
                'description' => 'Hang phong demo co tien nghi day du, phu hop de kiem tra dat phong va tinh kha dung.',
                'max_guests' => $guests,
                'bed_type' => $bed,
                'area' => $area,
                'price_per_night' => $price + ($hotelIndex * 45000),
                'thumbnail' => $this->image("demo/rooms/hotel-{$hotelIndex}-room-{$typeIndex}.svg", $name, $hotel->name, $hotelIndex + $typeIndex + 8),
                'status' => 'active',
            ]);

            $roomType->amenities()->sync(collect($this->roomAmenities)->take(5)->shuffle()->take(4)->all());

            RoomTypeImage::create([
                'room_type_id' => $roomType->id,
                'image_path' => $this->image("demo/rooms/hotel-{$hotelIndex}-room-{$typeIndex}-gallery.svg", $name, 'Room Gallery', $hotelIndex + $typeIndex + 12),
                'is_thumbnail' => false,
                'sort_order' => 1,
            ]);

            $roomCount = [8, 6, 4][$typeIndex];
            for ($i = 1; $i <= $roomCount; $i++) {
                Room::create([
                    'hotel_id' => $hotel->id,
                    'room_type_id' => $roomType->id,
                    'room_number' => ($typeIndex + 1) . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                    'floor' => (string) ($typeIndex + 1),
                    'status' => $i === $roomCount && $typeIndex === 1 ? 'maintenance' : 'available',
                    'note' => $i === $roomCount && $typeIndex === 1 ? 'Phong demo dang bao tri.' : null,
                ]);
            }
        }
    }

    private function seedBookings(array $hotels, array $customers, User $admin): void
    {
        $main = $hotels[0];
        $coast = $hotels[2];
        $city = $hotels[6];
        $island = $hotels[7];

        $this->bookingScenario($main, $customers[0], 'TMDEMO-HOLD-FUTURE', 'pending_payment', 7, 9, 'pending', null, ['hold_expires_at' => now()->addMinutes(15)]);
        $this->bookingScenario($main, $customers[1], 'TMDEMO-HOLD-EXPIRED', 'payment_expired', 6, 8, 'expired', null, ['hold_expires_at' => now()->subMinutes(5)]);
        $this->bookingScenario($main, $customers[2], 'TMDEMO-PAYMENT-FAILED', 'payment_failed', 10, 12, 'failed');
        $this->bookingScenario($coast, $customers[0], 'TMDEMO-PAID-CANCELABLE', 'confirmed', 14, 16, 'paid', 'temporary_recorded');
        $this->bookingScenario($coast, $customers[1], 'TMDEMO-CHECKIN-TODAY', 'confirmed', 0, 2, 'paid', 'temporary_recorded');
        $this->bookingScenario($coast, $customers[2], 'TMDEMO-STAYING', 'staying', -1, 2, 'paid', 'temporary_recorded', ['assign_room' => true, 'checked_in_at' => now()->subDay(), 'checkin_note' => 'Demo dang luu tru.']);
        $this->bookingScenario($city, $customers[3], 'TMDEMO-COMPLETED-REVIEWED', 'completed', -10, -8, 'paid', 'waiting_settlement', ['assign_room' => true, 'review' => true, 'checked_in_at' => now()->subDays(10), 'checked_out_at' => now()->subDays(8), 'checkout_note' => 'Demo checkout thanh cong.']);
        $this->bookingScenario($city, $customers[0], 'TMDEMO-NO-SHOW', 'no_show', -4, -2, 'paid', 'adjusted', ['no_show_at' => now()->subDays(4), 'no_show_reason' => 'Khach khong den nhan phong.']);
        $this->bookingScenario($main, $customers[3], 'TMDEMO-MANUAL-REVIEW', 'manual_review', 0, 1, 'paid', 'temporary_recorded', ['manual_review_reason' => 'Khong du phong vat ly san sang luc check-in.']);
        $this->bookingScenario($island, $customers[1], 'TMDEMO-REFUND-PENDING', 'cancelled', 12, 15, 'refunding', 'adjusted', ['cancelled_at' => now()->subHours(3), 'cancel_reason' => 'Khach yeu cau huy truoc ngay nhan phong.', 'refund_reason' => 'Khach gui yeu cau hoan tien.']);
        $this->bookingScenario($island, $customers[2], 'TMDEMO-REFUNDED-SETTLED', 'cancelled', -20, -18, 'refunded', 'settled', ['cancelled_at' => now()->subDays(14), 'cancel_reason' => 'Hoan tien sau khi giao dich da settlement.', 'settled' => true, 'owner_adjustment' => true, 'admin' => $admin]);
        $this->bookingScenario($city, $customers[2], 'TMDEMO-NON-REFUNDABLE', 'cancelled', 3, 5, 'non_refundable', 'adjusted', ['cancelled_at' => now()->subDay(), 'cancel_reason' => 'Huy sat ngay nhan phong.', 'refund_note' => 'Khong hoan tien theo chinh sach khach san.', 'admin' => $admin]);
    }

    private function bookingScenario(Hotel $hotel, User $customer, string $code, string $status, int $checkinOffset, int $checkoutOffset, string $paymentStatus, ?string $financialStatus = null, array $options = []): void
    {
        $roomType = $hotel->roomTypes()->where('status', 'active')->orderBy('price_per_night')->first();
        $quantity = 1;
        $checkin = now()->addDays($checkinOffset)->startOfDay();
        $checkout = now()->addDays($checkoutOffset)->startOfDay();
        $nights = max(1, $checkin->diffInDays($checkout));
        $total = (float) $roomType->price_per_night * $quantity * $nights;

        $booking = Booking::create([
            'customer_id' => $customer->id,
            'hotel_id' => $hotel->id,
            'booking_code' => $code,
            'checkin_date' => $checkin->toDateString(),
            'checkout_date' => $checkout->toDateString(),
            'guest_count' => min(2, $roomType->max_guests),
            'contact_name' => $customer->name,
            'contact_phone' => $customer->phone ?: '0912000000',
            'contact_email' => $customer->email,
            'special_request' => 'Du lieu demo Travel Mate.',
            'total_amount' => $total,
            'status' => $status,
            'hold_expires_at' => $options['hold_expires_at'] ?? null,
            'checked_in_at' => $options['checked_in_at'] ?? null,
            'checked_out_at' => $options['checked_out_at'] ?? null,
            'checkin_note' => $options['checkin_note'] ?? null,
            'checkout_note' => $options['checkout_note'] ?? null,
            'cancel_reason' => $options['cancel_reason'] ?? null,
            'cancelled_at' => $options['cancelled_at'] ?? null,
            'no_show_reason' => $options['no_show_reason'] ?? null,
            'no_show_at' => $options['no_show_at'] ?? null,
            'manual_review_reason' => $options['manual_review_reason'] ?? null,
        ]);

        BookingRoomType::create([
            'booking_id' => $booking->id,
            'room_type_id' => $roomType->id,
            'quantity' => $quantity,
            'price_per_night' => $roomType->price_per_night,
            'nights' => $nights,
            'subtotal' => $total,
        ]);

        if (!in_array($paymentStatus, ['none'], true)) {
            Payment::create([
                'booking_id' => $booking->id,
                'method' => Str::contains($code, ['VNPAY', 'PAID']) ? 'vnpay' : 'fake',
                'status' => $paymentStatus,
                'amount' => $total,
                'transaction_code' => 'TMDEMO-' . Str::upper(Str::random(8)),
                'gateway_response_code' => $paymentStatus === 'paid' ? '00' : null,
                'gateway_payload' => ['demo' => true, 'booking_code' => $code],
                'refund_amount' => in_array($paymentStatus, ['refunding', 'refunded'], true) ? $total : 0,
                'refund_reason' => $options['refund_reason'] ?? null,
                'refund_note' => $options['refund_note'] ?? ($paymentStatus === 'refunded' ? 'Admin da hoan tien demo.' : null),
                'paid_at' => in_array($paymentStatus, ['paid', 'refunding', 'refunded', 'non_refundable'], true) ? now()->subDays(max(1, abs($checkinOffset))) : null,
                'refunded_at' => in_array($paymentStatus, ['refunded', 'non_refundable'], true) ? now()->subDays(2) : null,
                'refunded_by' => isset($options['admin']) && in_array($paymentStatus, ['refunded', 'non_refundable'], true) ? $options['admin']->id : null,
            ]);
        }

        $financial = null;
        if ($financialStatus) {
            $financial = FinancialTransaction::create([
                'booking_id' => $booking->id,
                'owner_id' => $hotel->owner_id,
                'gross_amount' => $total,
                'platform_fee' => round($total * 0.15, 2),
                'owner_amount' => round($total * 0.85, 2),
                'status' => $financialStatus,
                'note' => 'Demo financial transaction.',
            ]);
        }

        if (!empty($options['assign_room'])) {
            $room = $roomType->rooms()->where('status', 'available')->first();
            if ($room) {
                BookingRoomAssignment::create([
                    'booking_id' => $booking->id,
                    'room_id' => $room->id,
                    'assigned_at' => $booking->checked_in_at ?: now()->subDay(),
                    'released_at' => $booking->checked_out_at,
                    'note' => 'Gan phong demo.',
                ]);

                $room->update(['status' => $status === 'staying' ? 'occupied' : 'cleaning']);
            }
        }

        if (!empty($options['review'])) {
            $review = Review::create([
                'booking_id' => $booking->id,
                'hotel_id' => $hotel->id,
                'customer_id' => $customer->id,
                'rating' => 5,
                'comment' => 'Phong sach, vi tri dep, quy trinh dat phong va thanh toan demo rat ro rang.',
                'status' => 'visible',
            ]);

            ReviewReply::create([
                'review_id' => $review->id,
                'owner_id' => $hotel->owner_id,
                'reply' => 'Cam on quy khach da trai nghiem dich vu tai Travel Mate Demo.',
            ]);
        }

        if (!empty($options['settled']) && $financial) {
            Settlement::create([
                'financial_transaction_id' => $financial->id,
                'owner_id' => $hotel->owner_id,
                'admin_id' => $options['admin']->id ?? null,
                'amount' => $financial->owner_amount,
                'status' => 'settled',
                'bank_name' => $hotel->owner->bank_name,
                'bank_account_number' => $hotel->owner->bank_account_number,
                'bank_account_name' => $hotel->owner->bank_account_name,
                'transfer_code' => 'TMSETTLED' . Str::upper(Str::random(5)),
                'note' => 'Settlement demo da chot.',
                'settled_at' => now()->subDays(12),
            ]);
        }

        if (!empty($options['owner_adjustment']) && $financial) {
            OwnerAdjustment::create([
                'owner_id' => $hotel->owner_id,
                'booking_id' => $booking->id,
                'financial_transaction_id' => $financial->id,
                'type' => 'refund_clawback',
                'amount' => round($financial->owner_amount, 2),
                'remaining_amount' => round($financial->owner_amount, 2),
                'status' => 'pending_deduction',
                'reason' => 'Demo refund sau settlement, can tru vao ky doi soat sau.',
                'created_by' => $options['admin']->id ?? null,
            ]);
        }
    }

    private function refreshHotelRatings(): void
    {
        Hotel::where('name', 'like', 'Travel Mate Demo - %')->get()->each(function (Hotel $hotel) {
            $visibleReviews = Review::where('hotel_id', $hotel->id)->where('status', 'visible');
            $count = (clone $visibleReviews)->count();
            if ($count > 0) {
                $hotel->update([
                    'average_rating' => round((clone $visibleReviews)->avg('rating'), 2),
                    'review_count' => $count,
                ]);
            }
        });
    }

    private function image(string $path, string $title, string $subtitle, int $seed): string
    {
        $photoPath = preg_replace('/\.svg$/i', '.jpg', $path) ?: $path;

        if (!Storage::disk('public')->exists($photoPath) && $this->downloadPhoto($photoPath, $seed, Str::contains($path, '/rooms/'))) {
            return $photoPath;
        }

        if (Storage::disk('public')->exists($photoPath)) {
            return $photoPath;
        }

        if (!Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, $this->svg($title, $subtitle, $seed));
        }

        return $path;
    }

    private function downloadPhoto(string $path, int $seed, bool $isRoom): bool
    {
        $urls = $isRoom ? $this->roomPhotoUrls : $this->hotelPhotoUrls;
        $url = $urls[$seed % count($urls)];

        try {
            $response = Http::timeout(20)->retry(2, 500)->get($url);
        } catch (\Throwable) {
            return false;
        }

        if (!$response->ok() || !Str::startsWith((string) $response->header('Content-Type'), 'image/')) {
            return false;
        }

        Storage::disk('public')->put($path, $response->body());

        return true;
    }

    private function svg(string $title, string $subtitle, int $seed): string
    {
        $palettes = [
            ['#0B1F2A', '#55D6C2', '#F6C453'],
            ['#102A43', '#7DD3FC', '#C4B5FD'],
            ['#164E63', '#A7F3D0', '#FDE68A'],
            ['#312E81', '#67E8F9', '#F9A8D4'],
            ['#1E3A8A', '#BAE6FD', '#FBBF24'],
        ];

        [$dark, $mid, $accent] = $palettes[$seed % count($palettes)];
        $safeTitle = e($title);
        $safeSubtitle = e($subtitle);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="760" viewBox="0 0 1200 760">
  <defs>
    <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="{$dark}"/>
      <stop offset="58%" stop-color="{$mid}"/>
      <stop offset="100%" stop-color="{$accent}"/>
    </linearGradient>
    <filter id="shadow" x="-20%" y="-20%" width="140%" height="140%">
      <feDropShadow dx="0" dy="24" stdDeviation="24" flood-color="#0f172a" flood-opacity=".28"/>
    </filter>
  </defs>
  <rect width="1200" height="760" fill="url(#g)"/>
  <circle cx="1040" cy="110" r="170" fill="#ffffff" opacity=".18"/>
  <circle cx="135" cy="650" r="230" fill="#ffffff" opacity=".16"/>
  <path d="M0 610 C210 500 315 670 500 560 C690 445 835 525 1200 410 L1200 760 L0 760 Z" fill="#ffffff" opacity=".22"/>
  <g filter="url(#shadow)">
    <rect x="145" y="150" width="890" height="430" rx="34" fill="#fffdf8" opacity=".92"/>
    <rect x="195" y="220" width="260" height="250" rx="20" fill="{$dark}" opacity=".9"/>
    <rect x="500" y="220" width="380" height="42" rx="21" fill="{$mid}" opacity=".55"/>
    <rect x="500" y="300" width="280" height="30" rx="15" fill="{$accent}" opacity=".7"/>
    <rect x="500" y="360" width="430" height="24" rx="12" fill="#0f172a" opacity=".12"/>
    <rect x="500" y="405" width="370" height="24" rx="12" fill="#0f172a" opacity=".1"/>
  </g>
  <text x="600" y="675" text-anchor="middle" font-family="Arial, sans-serif" font-size="38" font-weight="800" fill="#ffffff">{$safeTitle}</text>
  <text x="600" y="718" text-anchor="middle" font-family="Arial, sans-serif" font-size="22" font-weight="700" fill="#ffffff" opacity=".9">{$safeSubtitle}</text>
</svg>
SVG;
    }
}
