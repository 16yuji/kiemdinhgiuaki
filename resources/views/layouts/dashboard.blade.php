<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Dashboard') - Travel Mate</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.8/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="{{ asset('css/travel-mate-theme.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="tm-dashboard">
@php
    $portalLabel = auth()->user()->role === 'admin' ? 'Admin Portal' : 'Owner Portal';
    $portalSubtitle = auth()->user()->role === 'admin'
        ? 'Điều phối người dùng, duyệt khách sạn, hoàn tiền và đối soát.'
        : 'Quản lý cơ sở lưu trú, phòng, đơn đặt và doanh thu.';
@endphp
<div class="tm-dashboard-layout">
    <aside class="tm-sidebar">
        <div class="tm-sidebar-brand">
            <a href="{{ route('dashboard') }}" class="tm-brand text-white">
                <span class="tm-logo"><i class="bi bi-compass-fill"></i></span>
                <span class="tm-brand-text">
                    <strong class="text-white">Travel Mate</strong>
                    <small class="text-white-50">{{ $portalLabel }}</small>
                </span>
            </a>
        </div>

        <div class="tm-sidebar-nav">
            @if(auth()->user()->role === 'admin')
                @include('partials.sidebar-admin')
            @elseif(auth()->user()->role === 'owner')
                @include('partials.sidebar-owner')
            @endif
        </div>

        <div class="position-absolute bottom-0 start-0 end-0 p-3" style="z-index:2;">
            <div class="p-3 rounded-4" style="background: rgba(255,255,255,.11); border: 1px solid rgba(255,255,255,.14); backdrop-filter: blur(18px);">
                <div class="d-flex align-items-center gap-3">
                    <div class="tm-logo" style="width:42px;height:42px;border-radius:15px;">{{ mb_substr(auth()->user()->name, 0, 1) }}</div>
                    <div class="min-w-0">
                        <div class="fw-bold text-white text-truncate">{{ auth()->user()->name }}</div>
                        <div class="small text-white-50">Online • {{ ucfirst(auth()->user()->role) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <div class="tm-dashboard-main">
        <nav class="navbar tm-dashboard-topbar">
            <div class="container-fluid px-4">
                <div>
                    <div class="tm-eyebrow mb-1"><i class="bi bi-stars"></i> {{ $portalLabel }}</div>
                    <h1 class="h4 fw-black mb-0" style="font-weight:900;letter-spacing:0;">@yield('page-title', 'Dashboard')</h1>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('customer.home') }}" class="btn tm-btn-light d-none d-lg-inline-flex">
                        <i class="bi bi-globe2 me-1"></i> Website
                    </a>
                    <a href="{{ route('profile.edit') }}" class="btn tm-btn-light">
                        <i class="bi bi-person-circle me-1"></i>
                        <span class="d-none d-md-inline">{{ auth()->user()->name }}</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="js-confirm-form" data-confirm="Bạn muốn đăng xuất khỏi Travel Mate?">
                        @csrf
                        <button class="btn btn-outline-danger" type="submit">
                            <i class="bi bi-box-arrow-right"></i>
                            <span class="d-none d-md-inline">Đăng xuất</span>
                        </button>
                    </form>
                </div>
            </div>
        </nav>

        <main class="tm-dashboard-content">
            <div class="tm-page-hero" data-aos="fade-up">
                <div class="row align-items-center g-3">
                    <div class="col-lg-8">
                        <div class="tm-eyebrow"><i class="bi bi-compass"></i> Travel Mate Workspace</div>
                        <h2 class="tm-heading-md mb-2">Xin chào, {{ auth()->user()->name }}.</h2>
                        <p class="tm-lead mb-0">{{ $portalSubtitle }}</p>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill" style="background:rgba(255,255,255,.72);border:1px solid rgba(6,13,170,.11);font-weight:900;">
                            <i class="bi bi-calendar2-week text-primary"></i>
                            {{ now()->format('d/m/Y') }}
                        </div>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success" data-aos="fade-up">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger" data-aos="fade-up">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                </div>
            @endif

            <div data-aos="fade-up" data-aos-delay="80">
                @yield('content')
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.8/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vanilla-tilt@1.8.1/dist/vanilla-tilt.min.js"></script>
<script src="{{ asset('js/travel-mate-effects.js') }}"></script>
@stack('scripts')
</body>
</html>
