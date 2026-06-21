@extends('layouts.auth-bootstrap')

@section('title', 'Đăng nhập')
@section('subtitle', 'Vào tài khoản khách hàng, Owner hoặc Admin của bạn')
@section('auth-body-class', 'tm-login-lower')

@section('content')
<form method="POST" action="{{ route('login') }}" class="tm-auth-form">
    @csrf

    <div class="tm-field mb-3">
        <label for="email"><i class="bi bi-envelope me-1"></i>Email</label>
        <input
            id="email"
            type="email"
            name="email"
            value="{{ old('email') }}"
            class="form-control @error('email') is-invalid @enderror"
            placeholder="you@example.com"
            required
            autofocus
            autocomplete="username"
        >
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="tm-field mb-3">
        <label for="password"><i class="bi bi-lock me-1"></i>Mật khẩu</label>
        <input
            id="password"
            type="password"
            name="password"
            class="form-control @error('password') is-invalid @enderror"
            placeholder="Nhập mật khẩu"
            required
            autocomplete="current-password"
        >
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4 gap-3">
        <div class="form-check tm-check">
            <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
            <label for="remember_me" class="form-check-label">Ghi nhớ đăng nhập</label>
        </div>

        @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="tm-auth-inline-link">Quên mật khẩu?</a>
        @endif
    </div>

    <button type="submit" class="btn tm-btn-primary w-100 py-3">
        <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập
    </button>
</form>

<div class="tm-divider">hoặc</div>

<a href="{{ route('auth.google.redirect') }}" class="tm-google-btn tm-google-btn-lg mb-3">
    <span class="tm-google-icon">G</span>
    <span>Đăng nhập với Google</span>
    <i class="bi bi-arrow-right"></i>
</a>

<div class="tm-auth-switch">
    <span>Chưa có tài khoản?</span>
    <a href="{{ route('register') }}">Đăng ký ngay</a>
</div>
@endsection
