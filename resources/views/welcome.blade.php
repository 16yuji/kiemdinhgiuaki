@extends('layouts.frontend')

@section('title', 'Travel Mate - Đặt phòng khách sạn')

@section('content')
<section class="tm-resort-hero">
    <div class="tm-resort-hero-media"></div>
    <div class="container">
        <div class="tm-resort-hero-content" data-aos="fade-up">
            <span class="tm-resort-kicker">Travel Mate Collection</span>
            <h1>Đặt kỳ nghỉ đẹp, rõ giá và dễ quản lý.</h1>
            <p>
                Khám phá khách sạn, resort và homestay đang mở bán. Giữ phòng 15 phút, thanh toán VNPAY sandbox hoặc demo fallback, theo dõi hủy hoàn ngay trong tài khoản.
            </p>
            <div class="d-flex flex-wrap gap-3">
                <a href="{{ route('customer.hotels.index') }}" class="btn tm-resort-btn tm-resort-btn-gold">
                    <i class="bi bi-search me-2"></i>Tìm khách sạn
                </a>
                @guest
                    <a href="{{ route('register') }}" class="btn tm-resort-btn tm-resort-btn-glass">
                        Tạo tài khoản
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" class="btn tm-resort-btn tm-resort-btn-glass">
                        Vào trang của tôi
                    </a>
                @endguest
            </div>
        </div>

        <form action="{{ route('customer.hotels.index') }}" method="GET" class="tm-resort-search" data-aos="fade-up" data-aos-delay="120">
            <div>
                <label>Điểm đến</label>
                <input type="text" name="location" placeholder="Tên khách sạn, quận, tỉnh..." value="{{ request('location') }}">
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
            <button type="submit">
                <i class="bi bi-search"></i>
                Tìm phòng
            </button>
        </form>
    </div>
</section>

<section class="tm-resort-section tm-resort-intro">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-5" data-aos="fade-right">
                <span class="tm-resort-kicker">A better hotel journey</span>
                <h2 class="tm-resort-title">Một giao diện đặt phòng kiểu resort, tập trung vào ảnh, phòng và hành động.</h2>
            </div>
            <div class="col-lg-7" data-aos="fade-left">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="tm-resort-stat">
                            <strong>15 phút</strong>
                            <span>giữ chỗ khi chờ thanh toán</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="tm-resort-stat">
                            <strong>VNPAY</strong>
                            <span>sandbox và thanh toán demo</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="tm-resort-stat">
                            <strong>Admin</strong>
                            <span>xem xét hoàn tiền theo chính sách</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="featured" class="tm-resort-section pt-0">
    <div class="container">
        <div class="tm-resort-heading-row" data-aos="fade-up">
            <div>
                <span class="tm-resort-kicker">Featured stays</span>
                <h2 class="tm-resort-title">Khách sạn nổi bật</h2>
            </div>
            <a href="{{ route('customer.hotels.index') }}" class="tm-resort-link">Xem tất cả <i class="bi bi-arrow-right"></i></a>
        </div>

        <div class="row g-4">
            @forelse(($hotels ?? collect()) as $hotel)
                <div class="col-md-6 col-xl-4" data-aos="fade-up" data-aos-delay="{{ $loop->index * 70 }}">
                    <article class="tm-resort-hotel-card">
                        <a href="{{ route('customer.hotels.show', $hotel) }}" class="tm-resort-card-media">
                            @if($hotel->thumbnail)
                                <img src="{{ asset('storage/' . $hotel->thumbnail) }}" alt="{{ $hotel->name }}">
                            @else
                                <span>Travel Mate</span>
                            @endif
                            <em><i class="bi bi-star-fill"></i>{{ number_format($hotel->average_rating ?? 0, 1) }}</em>
                        </a>
                        <div class="tm-resort-card-body">
                            <span>{{ $hotel->district }}, {{ $hotel->province }}</span>
                            <h3>{{ $hotel->name }}</h3>
                            <p>{{ \Illuminate\Support\Str::limit($hotel->description, 105) }}</p>
                            <a href="{{ route('customer.hotels.show', $hotel) }}">Xem chi tiết</a>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12">
                    <div class="tm-resort-empty">
                        <i class="bi bi-buildings"></i>
                        Chưa có khách sạn nổi bật. Hãy vào trang khách sạn để xem các cơ sở đang hoạt động.
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>

<section id="how-it-works" class="tm-resort-section tm-resort-steps-section">
    <div class="container">
        <div class="tm-resort-heading-row" data-aos="fade-up">
            <div>
                <span class="tm-resort-kicker">How it works</span>
                <h2 class="tm-resort-title text-white">Đặt phòng trong ba bước</h2>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4" data-aos="fade-up">
                <div class="tm-resort-step">
                    <b>01</b>
                    <h3>Tìm nơi phù hợp</h3>
                    <p>Lọc theo điểm đến, ngày lưu trú, số khách, giá, đánh giá và tiện nghi.</p>
                </div>
            </div>
            <div class="col-lg-4" data-aos="fade-up" data-aos-delay="80">
                <div class="tm-resort-step">
                    <b>02</b>
                    <h3>Thanh toán rõ ràng</h3>
                    <p>Chọn VNPAY sandbox hoặc thanh toán giả lập cho demo. Khách hàng chỉ thấy tổng tiền và trạng thái thanh toán.</p>
                </div>
            </div>
            <div class="col-lg-4" data-aos="fade-up" data-aos-delay="160">
                <div class="tm-resort-step">
                    <b>03</b>
                    <h3>Lưu trú và đánh giá</h3>
                    <p>Owner vận hành check-in/check-out, khách hàng đánh giá sau khi hoàn tất lưu trú.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="tm-resort-section">
    <div class="container">
        <div class="tm-resort-partner" data-aos="zoom-in">
            <div>
                <span class="tm-resort-kicker">For partners</span>
                <h2>Đưa khách sạn của bạn lên Travel Mate.</h2>
                <p>Customer có thể gửi yêu cầu trở thành đối tác. Admin duyệt trước khi tài khoản được chuyển sang Owner.</p>
            </div>
            @auth
                @if(auth()->user()->role === 'customer')
                    <a href="{{ route('customer.partner-request.create') }}" class="btn tm-resort-btn tm-resort-btn-gold">Gửi yêu cầu</a>
                @else
                    <a href="{{ route('dashboard') }}" class="btn tm-resort-btn tm-resort-btn-gold">Mở dashboard</a>
                @endif
            @else
                <a href="{{ route('register') }}" class="btn tm-resort-btn tm-resort-btn-gold">Bắt đầu</a>
            @endauth
        </div>
    </div>
</section>
@endsection
