@extends('layouts.frontend')

@section('title', $hotel->name . ' - Travel Mate')

@section('content')
@php
    $minRoomPrice = $roomTypes->where('available_count', '>', 0)->min('price_per_night');
    $availableRoomCount = $roomTypes->sum('available_count');
    $defaultRecommendationRoomType = $roomTypes->where('available_count', '>', 0)->first() ?? $roomTypes->first();
@endphp

<section class="tm-resort-detail-hero">
    <div class="container">
        <div class="tm-resort-detail-title" data-aos="fade-up">
            <span class="tm-resort-kicker">Travel Mate Collection</span>
            <h1>{{ $hotel->name }}</h1>
            <p><i class="bi bi-geo-alt me-1"></i>{{ $hotel->address }}, {{ $hotel->ward }}, {{ $hotel->district }}, {{ $hotel->province }}</p>
            <div class="tm-resort-detail-meta">
                <span><i class="bi bi-star-fill"></i>{{ number_format($hotel->average_rating ?? 0, 1) }} · {{ $hotel->review_count ?? 0 }} đánh giá</span>
                <span>Nhận {{ $hotel->checkin_time ? \Carbon\Carbon::parse($hotel->checkin_time)->format('H:i') : '14:00' }}</span>
                <span>Trả {{ $hotel->checkout_time ? \Carbon\Carbon::parse($hotel->checkout_time)->format('H:i') : '12:00' }}</span>
                <span>{{ \Carbon\Carbon::parse($checkinDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($checkoutDate)->format('d/m/Y') }}</span>
            </div>
        </div>

        <div class="tm-resort-gallery" data-aos="zoom-in">
            <div class="tm-resort-gallery-main">
                @if($hotel->thumbnail)
                    <img src="{{ asset('storage/' . $hotel->thumbnail) }}" alt="{{ $hotel->name }}">
                @else
                    <span>Travel Mate</span>
                @endif
            </div>
            <div class="tm-resort-gallery-side">
                @forelse($hotel->images->take(3) as $image)
                    <img src="{{ asset('storage/' . $image->path) }}" alt="{{ $hotel->name }}">
                @empty
                    <div>Hotel</div>
                    <div>Resort</div>
                    <div>Stay</div>
                @endforelse
            </div>
        </div>
    </div>
</section>

<section class="tm-resort-section tm-resort-detail-content">
    <div class="container">
        <div class="row g-5 align-items-start">
            <div class="col-lg-8">
                <div class="tm-resort-info-block" data-aos="fade-up">
                    <span class="tm-resort-kicker">About this stay</span>
                    <h2>Về khách sạn</h2>
                    <p>{{ $hotel->description ?: 'Khách sạn chưa có mô tả.' }}</p>
                </div>

                <div class="tm-resort-info-block" data-aos="fade-up" data-aos-delay="80">
                    <span class="tm-resort-kicker">Amenities</span>
                    <h2>Tiện nghi nổi bật</h2>
                    <div class="tm-resort-tag-row">
                        @forelse($hotel->amenities as $amenity)
                            <b><i class="bi bi-check2-circle me-1"></i>{{ $amenity->name }}</b>
                        @empty
                            <span class="tm-resort-muted">Chưa có tiện nghi.</span>
                        @endforelse
                    </div>
                </div>

                <div class="tm-resort-info-block" data-aos="fade-up" data-aos-delay="120">
                    <span class="tm-resort-kicker">Cancellation policy</span>
                    <h2>Chính sách hủy / hoàn tiền</h2>
                    <p>{{ $hotel->cancellation_policy ?: 'Khách sạn chưa cập nhật chính sách hủy / hoàn tiền. Vui lòng liên hệ cơ sở lưu trú để biết thêm chi tiết.' }}</p>
                </div>

                @include('shared.hotel-map', [
                    'hotel' => $hotel,
                    'mapId' => 'customer-hotel-detail-map',
                    'heading' => 'Vi tri khach san',
                    'class' => 'mb-4',
                ])

                <div class="tm-resort-heading-row mt-5" data-aos="fade-up">
                    <div>
                        <span class="tm-resort-kicker">Room types</span>
                        <h2 class="tm-resort-title">Chọn hạng phòng</h2>
                    </div>
                </div>

                <div class="tm-resort-room-list">
                    @forelse($roomTypes as $roomType)
                        <article class="tm-resort-room-card" data-aos="fade-up" data-aos-delay="{{ ($loop->index % 4) * 70 }}">
                            <div class="tm-resort-room-image">
                                @if($roomType->thumbnail)
                                    <img src="{{ asset('storage/' . $roomType->thumbnail) }}" alt="{{ $roomType->name }}">
                                @else
                                    <span>Room</span>
                                @endif
                            </div>

                            <div class="tm-resort-room-body">
                                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                    <div>
                                        <h3>{{ $roomType->name }}</h3>
                                        <p>{{ $roomType->description ?: 'Chưa có mô tả.' }}</p>
                                    </div>
                                    <strong>{{ number_format($roomType->price_per_night, 0, ',', '.') }}đ / đêm</strong>
                                </div>

                                <div class="tm-resort-tag-row">
                                    <b>{{ $roomType->max_guests }} khách</b>
                                    <b>{{ $roomType->bed_type ?: 'Chưa nhập giường' }}</b>
                                    @if($roomType->area)
                                        <b>{{ $roomType->area }} m²</b>
                                    @endif
                                    @foreach($roomType->amenities->take(4) as $amenity)
                                        <b>{{ $amenity->name }}</b>
                                    @endforeach
                                </div>

                                <div class="tm-resort-room-action">
                                    <span>Còn {{ $roomType->available_count }} phòng</span>
                                    <button
                                        type="button"
                                        class="tm-ai-room-trigger js-ai-room-trigger"
                                        data-room-name="{{ $roomType->name }}"
                                        data-endpoint="{{ route('ai.room-recommendations.index', [
                                            'roomType' => $roomType,
                                            'checkin_date' => $checkinDate,
                                            'checkout_date' => $checkoutDate,
                                            'guests' => $guests,
                                        ]) }}"
                                        data-target="hotel-room-recommendations"
                                    >
                                        <i class="bi bi-stars"></i> Gợi ý tương tự
                                    </button>

                                    @if($roomType->available_count <= 0)
                                        <button class="btn tm-resort-btn tm-resort-btn-disabled" disabled>Hết phòng</button>
                                    @else
                                        @guest
                                            <a href="{{ route('login') }}" class="btn tm-resort-btn tm-resort-btn-light">Đăng nhập để đặt</a>
                                        @else
                                            @if(auth()->user()->role === 'customer')
                                                <a href="{{ route('customer.bookings.create', [
                                                    'hotel_id' => $hotel->id,
                                                    'room_type_id' => $roomType->id,
                                                    'checkin_date' => $checkinDate,
                                                    'checkout_date' => $checkoutDate,
                                                    'guests' => $guests
                                                ]) }}" class="btn tm-resort-btn tm-resort-btn-dark">Đặt phòng</a>
                                            @else
                                                <button class="btn tm-resort-btn tm-resort-btn-disabled" disabled title="Chỉ tài khoản khách hàng mới được đặt phòng">
                                                    Chỉ khách hàng được đặt
                                                </button>
                                            @endif
                                        @endguest
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="tm-resort-empty">
                            <i class="bi bi-door-closed"></i>
                            Không có hạng phòng phù hợp với ngày và số khách đã chọn.
                        </div>
                    @endforelse
                </div>

                @if($defaultRecommendationRoomType)
                    <div
                        id="hotel-room-recommendations"
                        class="tm-ai-room-panel js-ai-room-panel"
                        data-endpoint="{{ route('ai.room-recommendations.index', [
                            'roomType' => $defaultRecommendationRoomType,
                            'checkin_date' => $checkinDate,
                            'checkout_date' => $checkoutDate,
                            'guests' => $guests,
                        ]) }}"
                    >
                        <div class="tm-ai-room-panel-head">
                            <div>
                                <span class="tm-resort-kicker">Smart suggestions</span>
                                <h2 class="tm-resort-title">Có thể bạn cũng thích</h2>
                            </div>
                            <small>Dựa trên giá, tiện nghi, sức chứa, vị trí và phòng còn trống.</small>
                        </div>
                        <div class="tm-ai-room-results js-ai-room-results">
                            <div class="tm-ai-loading">
                                <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                                Đang tính gợi ý phù hợp...
                            </div>
                        </div>
                    </div>
                @endif

                <div class="tm-resort-info-block mt-5" data-aos="fade-up">
                    <span class="tm-resort-kicker">Guest reviews</span>
                    <h2>Đánh giá từ khách hàng</h2>

                    @if($reviewSummary)
                        <div class="tm-ai-review-summary">
                            <div class="tm-ai-review-summary-head">
                                <span><i class="bi bi-stars"></i> AI tóm tắt đánh giá</span>
                                <small>Dựa trên {{ $reviewSummary->review_count }} đánh giá công khai</small>
                            </div>
                            <p>{{ $reviewSummary->summary }}</p>
                            <div class="tm-ai-review-grid">
                                <div>
                                    <strong>Điểm nổi bật</strong>
                                    @foreach(($reviewSummary->pros ?? []) as $pro)
                                        <span><i class="bi bi-check2-circle"></i>{{ $pro }}</span>
                                    @endforeach
                                </div>
                                <div>
                                    <strong>Cần lưu ý</strong>
                                    @foreach(($reviewSummary->cons ?? []) as $con)
                                        <span><i class="bi bi-info-circle"></i>{{ $con }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    @forelse($hotel->reviews->where('status', 'visible') as $review)
                        <div class="tm-resort-review">
                            <div>
                                <strong>{{ $review->customer->name ?? 'Khách hàng' }}</strong>
                                <span><i class="bi bi-star-fill"></i>{{ $review->rating }}/5</span>
                            </div>
                            <p>{{ $review->comment ?: 'Khách hàng không để lại nhận xét.' }}</p>

                            @if($review->reply)
                                <blockquote>
                                    <strong>Phản hồi từ chủ cơ sở:</strong>
                                    {{ $review->reply->reply }}
                                </blockquote>
                            @endif
                        </div>
                    @empty
                        <p class="tm-resort-muted">Khách sạn chưa có đánh giá.</p>
                    @endforelse
                </div>
            </div>

            <div class="col-lg-4">
                <aside class="tm-resort-booking-aside" data-aos="fade-left">
                    <span class="tm-resort-kicker">Check availability</span>
                    <h2>Kiểm tra phòng</h2>

                    <div class="tm-resort-aside-summary">
                        <div><span>Hạng phòng phù hợp</span><strong>{{ $roomTypes->where('available_count', '>', 0)->count() }}</strong></div>
                        <div><span>Tổng phòng còn</span><strong>{{ $availableRoomCount }}</strong></div>
                        <div><span>Giá từ</span><strong>{{ $minRoomPrice ? number_format($minRoomPrice, 0, ',', '.') . 'đ / đêm' : 'Hết phòng' }}</strong></div>
                    </div>

                    <form method="GET" action="{{ route('customer.hotels.show', $hotel) }}">
                        <label>Ngày nhận</label>
                        <input type="date" name="checkin_date" value="{{ $checkinDate }}" min="{{ now()->toDateString() }}">

                        <label>Ngày trả</label>
                        <input type="date" name="checkout_date" value="{{ $checkoutDate }}" min="{{ now()->addDay()->toDateString() }}">

                        <label>Số khách</label>
                        <input type="number" name="guests" min="1" value="{{ $guests }}">

                        <button type="submit"><i class="bi bi-search me-1"></i>Kiểm tra phòng</button>
                    </form>

                    <a href="{{ route('customer.hotels.index', [
                        'checkin_date' => $checkinDate,
                        'checkout_date' => $checkoutDate,
                        'guests' => $guests
                    ]) }}" class="tm-resort-back-link">
                        <i class="bi bi-arrow-left me-1"></i>Quay lại tìm kiếm
                    </a>
                </aside>
            </div>
        </div>
    </div>
</section>
@endsection
