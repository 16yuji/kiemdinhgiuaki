@extends('layouts.frontend')

@section('title', 'Trang chủ - Travel Mate')

@section('content')
<section class="tm-resort-hero tm-resort-hero-home">
    <div class="tm-resort-hero-media"></div>
    <div class="container">
        <div class="tm-resort-hero-content" data-aos="fade-up">
            <span class="tm-resort-kicker">Welcome to Travel Mate</span>
            <h1>Chọn khách sạn theo cảm giác của chuyến đi.</h1>
            <p>
                Trang chủ dành cho khách hàng: tìm nơi ở, giữ chỗ, tiếp tục thanh toán và theo dõi lịch sử đặt phòng trong một trải nghiệm sạch, sang và rõ nghiệp vụ.
            </p>
            <div class="d-flex flex-wrap gap-3">
                <a href="{{ route('customer.hotels.index') }}" class="btn tm-resort-btn tm-resort-btn-gold">
                    <i class="bi bi-search me-2"></i>Khám phá nơi ở
                </a>
                @auth
                    @if(auth()->user()->role === 'customer')
                        <a href="{{ route('customer.bookings.history') }}" class="btn tm-resort-btn tm-resort-btn-glass">
                            Lịch sử đặt phòng
                        </a>
                    @endif
                @else
                    <a href="{{ route('register') }}" class="btn tm-resort-btn tm-resort-btn-glass">Tạo tài khoản</a>
                @endauth
            </div>
        </div>

        <form action="{{ route('customer.hotels.index') }}" method="GET" class="tm-resort-search tm-resort-search-wide" data-aos="fade-up" data-aos-delay="120">
            <div>
                <label>Điểm đến</label>
                <input type="text" name="location" placeholder="Bạn muốn đi đâu?" value="{{ request('location') }}">
            </div>
            <div>
                <label>Nhận phòng</label>
                <input type="date" name="checkin_date" value="{{ request('checkin_date', now()->toDateString()) }}" min="{{ now()->toDateString() }}">
            </div>
            <div>
                <label>Trả phòng</label>
                <input type="date" name="checkout_date" value="{{ request('checkout_date', now()->addDay()->toDateString()) }}" min="{{ now()->addDay()->toDateString() }}">
            </div>
            <div>
                <label>Số khách</label>
                <input type="number" name="guests" min="1" value="{{ request('guests', 1) }}">
            </div>
            <button type="submit"><i class="bi bi-search"></i>Tìm kiếm</button>
        </form>
    </div>
</section>

<section class="tm-resort-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4" data-aos="fade-up">
                <div class="tm-resort-feature">
                    <i class="bi bi-calendar2-check"></i>
                    <h3>Giữ phòng 15 phút</h3>
                    <p>Đơn pending payment giữ chỗ tạm thời, hết hạn sẽ được giải phóng.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="70">
                <div class="tm-resort-feature">
                    <i class="bi bi-credit-card"></i>
                    <h3>Thanh toán demo</h3>
                    <p>Giữ cả VNPAY sandbox và fallback giả lập để trình bày đồ án.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="140">
                <div class="tm-resort-feature">
                    <i class="bi bi-headset"></i>
                    <h3>Hỗ trợ rõ ràng</h3>
                    <p>Khi quá ngày nhận phòng, khách liên hệ Admin và khách sạn thay vì hủy online.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="featured" class="tm-resort-section pt-0">
    <div class="container">
        <div class="tm-resort-heading-row" data-aos="fade-up">
            <div>
                <span class="tm-resort-kicker">Curated stays</span>
                <h2 class="tm-resort-title">Gợi ý hôm nay</h2>
            </div>
            <a href="{{ route('customer.hotels.index') }}" class="tm-resort-link">Xem tất cả <i class="bi bi-arrow-right"></i></a>
        </div>

        <div class="row g-4">
            @forelse($hotels as $hotel)
                <div class="col-md-6 col-xl-4" data-aos="fade-up" data-aos-delay="{{ $loop->index * 70 }}">
                    <article class="tm-resort-hotel-card">
                        <a href="{{ route('customer.hotels.show', $hotel) }}" class="tm-resort-card-media">
                            @if($hotel->thumbnail)
                                <img src="{{ asset('storage/' . $hotel->thumbnail) }}" alt="{{ $hotel->name }}">
                            @else
                                <span>Travel Mate</span>
                            @endif
                            <em><i class="bi bi-star-fill"></i>{{ number_format($hotel->average_rating, 1) }}</em>
                        </a>
                        <div class="tm-resort-card-body">
                            <span>{{ $hotel->district }}, {{ $hotel->province }}</span>
                            <h3>{{ $hotel->name }}</h3>
                            <p>{{ \Illuminate\Support\Str::limit($hotel->description, 110) }}</p>
                            <div class="d-flex justify-content-between align-items-center gap-3">
                                <strong>Đang hoạt động</strong>
                                <a href="{{ route('customer.hotels.show', $hotel) }}">Xem chi tiết</a>
                            </div>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12">
                    <div class="tm-resort-empty">
                        <i class="bi bi-buildings"></i>
                        Chưa có khách sạn để hiển thị.
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>

<section class="tm-resort-section pt-0">
    <div class="container">
        <div class="tm-resort-editorial" data-aos="fade-up">
            <div class="tm-resort-editorial-image"></div>
            <div>
                <span class="tm-resort-kicker">Travel Mate Promise</span>
                <h2>Đẹp ở giao diện, chặt ở nghiệp vụ.</h2>
                <p>
                    Khách hàng chỉ thấy tổng tiền, trạng thái thanh toán và thông tin hỗ trợ. Các bước vận hành và tài chính nội bộ được xử lý trong khu vực phù hợp.
                </p>
                <a href="{{ route('customer.hotels.index') }}" class="tm-resort-link">Bắt đầu tìm khách sạn <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>
</section>
@endsection
