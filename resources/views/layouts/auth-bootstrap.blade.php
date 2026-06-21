<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Xác thực tài khoản') - Travel Mate</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link href="{{ asset('css/travel-mate-theme.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="tm-auth-resort @yield('auth-body-class')">
<main class="tm-auth-resort-wrap">
    <section class="tm-auth-resort-photo">
        <a href="{{ route('customer.home') }}" class="tm-resort-brand text-white text-decoration-none">
            <span class="tm-resort-brand-mark">TM</span>
            <span>
                <strong>Travel Mate</strong>
                <small>Hotel & Resort Booking</small>
            </span>
        </a>

        <div>
            <span class="tm-resort-kicker text-white-50">Secure booking account</span>
            <h1>Một tài khoản cho toàn bộ hành trình lưu trú.</h1>
            <p>Đặt phòng, giữ chỗ, thanh toán sandbox, theo dõi hủy hoàn và quản lý hồ sơ Travel Mate.</p>
        </div>
    </section>

    <section class="tm-auth-resort-panel">
        <div class="tm-auth-resort-box" data-aos="fade-up">
            <div class="mb-4">
                <span class="tm-resort-kicker">Travel Mate</span>
                <h2>@yield('title', 'Đăng nhập')</h2>
                <p>@yield('subtitle', 'Tiếp tục hành trình cùng Travel Mate')</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle me-1"></i>Cần kiểm tra lại thông tin</div>
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('status'))
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
                </div>
            @endif

            @yield('content')
        </div>
    </section>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vanilla-tilt@1.8.1/dist/vanilla-tilt.min.js"></script>
<script src="{{ asset('js/travel-mate-effects.js') }}"></script>
@stack('scripts')
</body>
</html>
