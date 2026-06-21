@extends('layouts.frontend')

@section('title', 'Tìm kiếm khách sạn - Travel Mate')

@section('content')
@php
    $activeFilterCount = collect([
        $location,
        $minPrice,
        $maxPrice,
        $minRating,
        !empty($selectedAmenityIds ?? []) ? 'amenities' : null,
    ])->filter(fn ($value) => filled($value))->count();
@endphp

<section class="tm-resort-page-hero tm-resort-search-hero">
    <div class="container">
        <div class="row g-4 align-items-end">
            <div class="col-lg-8" data-aos="fade-up">
                <span class="tm-resort-kicker">Search Travel Mate</span>
                <h1>Tìm khách sạn theo đúng lịch trình.</h1>
                <p>Lọc điểm đến, ngày ở, giá, tiện nghi và đánh giá. Kết quả được tính theo phòng còn khả dụng.</p>
            </div>
            <div class="col-lg-4 text-lg-end" data-aos="fade-up" data-aos-delay="80">
                <span class="tm-resort-count">{{ $hotels->count() }} kết quả trên trang này</span>
            </div>
        </div>
    </div>
</section>

<section class="tm-resort-section tm-resort-search-panel-section">
    <div class="container">
        <form method="GET" action="{{ route('customer.hotels.index') }}" class="tm-resort-filter" data-aos="fade-up">
            <div class="tm-resort-filter-main">
                <div>
                    <label>Điểm đến</label>
                    <input type="text" name="location" value="{{ $location }}" placeholder="Tỉnh, quận, tên khách sạn...">
                </div>
                <div>
                    <label>Ngày nhận</label>
                    <input type="date" name="checkin_date" value="{{ $checkinDate }}" min="{{ now()->toDateString() }}">
                </div>
                <div>
                    <label>Ngày trả</label>
                    <input type="date" name="checkout_date" value="{{ $checkoutDate }}" min="{{ now()->addDay()->toDateString() }}">
                </div>
                <div>
                    <label>Số khách</label>
                    <input type="number" name="guests" min="1" value="{{ $guests }}">
                </div>
                <button type="submit"><i class="bi bi-search"></i>Tìm kiếm</button>
            </div>

            <div class="tm-resort-filter-extra">
                <div>
                    <label>Giá từ</label>
                    <input type="number" name="min_price" min="0" value="{{ $minPrice }}" placeholder="300000">
                </div>
                <div>
                    <label>Giá đến</label>
                    <input type="number" name="max_price" min="0" value="{{ $maxPrice }}" placeholder="1000000">
                </div>
                <div>
                    <label>Đánh giá</label>
                    <select name="min_rating">
                        <option value="">Không chọn</option>
                        <option value="5" @selected((string) $minRating === '5')>5 sao</option>
                        <option value="4" @selected((string) $minRating === '4')>Từ 4 sao</option>
                        <option value="3" @selected((string) $minRating === '3')>Từ 3 sao</option>
                        <option value="2" @selected((string) $minRating === '2')>Từ 2 sao</option>
                        <option value="1" @selected((string) $minRating === '1')>Từ 1 sao</option>
                    </select>
                </div>
                <a href="{{ route('customer.hotels.index') }}">Xóa lọc</a>
            </div>

            <div class="tm-resort-amenities">
                <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-3">
                    <h2>Tiện nghi</h2>
                    @if($activeFilterCount > 0)
                        <span>{{ $activeFilterCount }} bộ lọc đang dùng</span>
                    @endif
                </div>
                <div class="row g-2">
                    @forelse($hotelAmenities as $amenity)
                        <div class="col-md-3 col-sm-6">
                            <label class="tm-resort-check">
                                <input type="checkbox" name="amenity_ids[]" value="{{ $amenity->id }}" @checked(in_array($amenity->id, $selectedAmenityIds ?? []))>
                                <span>{{ $amenity->name }}</span>
                            </label>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-warning mb-0">Chưa có dữ liệu tiện nghi khách sạn.</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </form>
    </div>
</section>

<section class="tm-resort-section pt-0">
    <div class="container">
        <div class="tm-resort-heading-row" data-aos="fade-up">
            <div>
                <span class="tm-resort-kicker">Available stays</span>
                <h2 class="tm-resort-title">Những lựa chọn đang còn phòng</h2>
            </div>
            <span class="tm-resort-muted">{{ $hotels->count() }} kết quả hiển thị</span>
        </div>

        <div class="tm-resort-results">
            @forelse($hotels as $hotel)
                <article class="tm-resort-result-card" data-aos="fade-up" data-aos-delay="{{ ($loop->index % 6) * 60 }}">
                    <a href="{{ route('customer.hotels.show', ['hotel' => $hotel, 'checkin_date' => $checkinDate, 'checkout_date' => $checkoutDate, 'guests' => $guests]) }}" class="tm-resort-result-image">
                        @if($hotel->thumbnail)
                            <img src="{{ asset('storage/' . $hotel->thumbnail) }}" alt="{{ $hotel->name }}">
                        @else
                            <span>Travel Mate</span>
                        @endif
                    </a>

                    <div class="tm-resort-result-body">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <span>{{ $hotel->address }}, {{ $hotel->district }}, {{ $hotel->province }}</span>
                                <h3>{{ $hotel->name }}</h3>
                            </div>
                            <em><i class="bi bi-star-fill"></i>{{ number_format($hotel->average_rating, 1) }}</em>
                        </div>

                        <div class="tm-resort-tag-row">
                            @foreach($hotel->amenities->take(5) as $amenity)
                                <b>{{ $amenity->name }}</b>
                            @endforeach
                        </div>

                        <div class="tm-resort-result-bottom">
                            <div>
                                <strong>Từ {{ number_format($hotel->min_price, 0, ',', '.') }}đ / đêm</strong>
                                <small>{{ $hotel->available_room_types_count }} hạng phòng phù hợp</small>
                            </div>
                            <a href="{{ route('customer.hotels.show', ['hotel' => $hotel, 'checkin_date' => $checkinDate, 'checkout_date' => $checkoutDate, 'guests' => $guests]) }}">
                                Xem phòng
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="tm-resort-empty">
                    <i class="bi bi-search"></i>
                    Không tìm thấy khách sạn phù hợp. Vui lòng thay đổi điểm đến, ngày lưu trú hoặc bộ lọc.
                </div>
            @endforelse
        </div>

        <div class="mt-4" data-aos="fade-up">
            {{ $hotels->links() }}
        </div>
    </div>
</section>
@endsection
