<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Travel Mate')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.8/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="{{ asset('css/travel-mate-theme.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body class="tm-resort-body">
<header class="tm-resort-header">
    <div class="tm-resort-topbar">
        <div class="container-fluid px-4 px-xl-5">
            <div class="d-flex justify-content-between align-items-center gap-3">
                <div class="d-flex flex-wrap align-items-center gap-3 small">
                    <span><i class="bi bi-telephone me-1"></i>1900 9999</span>
                    <span><i class="bi bi-envelope me-1"></i>support@travelmate.local</span>
                </div>
                <div class="d-none d-lg-flex align-items-center gap-3 small">
                    <span><i class="bi bi-shield-check me-1"></i>VNPAY sandbox</span>
                    <span><i class="bi bi-clock-history me-1"></i>Giữ phòng 15 phút</span>
                </div>
            </div>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg tm-resort-nav">
        <div class="container-fluid px-4 px-xl-5">
            <a class="navbar-brand tm-resort-brand" href="{{ route('customer.home') }}">
                <span class="tm-resort-brand-mark">TM</span>
                <span>
                    <strong>Travel Mate</strong>
                    <small>Hotel & Resort Booking</small>
                </span>
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav mx-lg-auto tm-resort-menu">
                    <li class="nav-item">
                        <a href="{{ route('customer.home') }}" class="nav-link">Trang chủ</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('customer.hotels.index') }}" class="nav-link">Khách sạn</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/#featured') }}" class="nav-link">Nổi bật</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/#how-it-works') }}" class="nav-link">Quy trình</a>
                    </li>
                </ul>

                <ul class="navbar-nav ms-auto align-items-lg-center tm-resort-actions">
                    @auth
                        @if(auth()->user()->role === 'admin')
                            <li class="nav-item"><a href="{{ route('admin.dashboard') }}" class="nav-link">Admin dashboard</a></li>
                        @elseif(auth()->user()->role === 'owner')
                            <li class="nav-item"><a href="{{ route('owner.dashboard') }}" class="nav-link">Owner dashboard</a></li>
                        @elseif(auth()->user()->role === 'customer')
                            <li class="nav-item"><a href="{{ route('customer.bookings.history') }}" class="nav-link">Lịch sử đặt phòng</a></li>
                            <li class="nav-item"><a href="{{ route('customer.partner-request.create') }}" class="nav-link">Trở thành đối tác</a></li>
                        @endif

                        <li class="nav-item">
                            <a href="{{ route('profile.edit') }}" class="btn tm-resort-btn tm-resort-btn-light">
                                <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}" class="js-confirm-form" data-confirm="Bạn muốn đăng xuất khỏi Travel Mate?">
                                @csrf
                                <button class="btn tm-resort-btn tm-resort-btn-outline" type="submit">
                                    <i class="bi bi-box-arrow-right me-1"></i>Đăng xuất
                                </button>
                            </form>
                        </li>
                    @else
                        <li class="nav-item">
                            <a href="{{ route('login') }}" class="btn tm-resort-btn tm-resort-btn-light">Đăng nhập</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('register') }}" class="btn tm-resort-btn tm-resort-btn-dark">Đăng ký</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>
</header>

<main class="tm-resort-main">
    <div class="container travel-alert-wrap">
        @if(session('success'))
            <div class="alert alert-success mt-3" data-aos="fade-down">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning mt-3" data-aos="fade-down">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger mt-3" data-aos="fade-down">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            </div>
        @endif
    </div>

    @yield('content')
</main>

<footer class="tm-resort-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <a class="tm-resort-brand text-white text-decoration-none" href="{{ route('customer.home') }}">
                    <span class="tm-resort-brand-mark">TM</span>
                    <span>
                        <strong>Travel Mate</strong>
                        <small>Hotel & Resort Booking</small>
                    </span>
                </a>
                <p class="mt-4 mb-0 text-white-50">
                    Nền tảng đặt phòng khách sạn với quy trình giữ chỗ, thanh toán sandbox, hủy hoàn và quản lý lưu trú rõ ràng.
                </p>
            </div>

            <div class="col-6 col-lg-2">
                <h3>Khám phá</h3>
                <a href="{{ route('customer.home') }}">Trang chủ</a>
                <a href="{{ route('customer.hotels.index') }}">Khách sạn</a>
                <a href="{{ url('/#featured') }}">Nổi bật</a>
            </div>

            <div class="col-6 col-lg-3">
                <h3>Tài khoản</h3>
                @guest
                    <a href="{{ route('login') }}">Đăng nhập</a>
                    <a href="{{ route('register') }}">Đăng ký</a>
                @else
                    <a href="{{ route('profile.edit') }}">Hồ sơ</a>
                    @if(auth()->user()->role === 'customer')
                        <a href="{{ route('customer.bookings.history') }}">Lịch sử đặt phòng</a>
                    @elseif(auth()->user()->role === 'owner')
                        <a href="{{ route('owner.dashboard') }}">Owner dashboard</a>
                    @elseif(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}">Admin dashboard</a>
                    @endif
                @endguest
            </div>

            <div class="col-lg-3">
                <h3>Hỗ trợ</h3>
                <p class="text-white-50 mb-2"><i class="bi bi-telephone me-2"></i>1900 9999</p>
                <p class="text-white-50 mb-2"><i class="bi bi-envelope me-2"></i>support@travelmate.local</p>
                <p class="text-white-50 mb-0"><i class="bi bi-geo-alt me-2"></i>Travel Mate Support Center</p>
            </div>
        </div>

        <div class="tm-resort-footer-bottom">
            <span>© {{ date('Y') }} Travel Mate.</span>
            <span>Designed as a premium hotel booking experience.</span>
        </div>
    </div>
</footer>

@include('shared.ai-chat-widget')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.8/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vanilla-tilt@1.8.1/dist/vanilla-tilt.min.js"></script>
<script src="{{ asset('js/travel-mate-effects.js') }}"></script>
@stack('scripts')
</body>
</html>
