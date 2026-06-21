@extends('layouts.auth-bootstrap')

@section('title', 'Đăng ký')
@section('subtitle', 'Tạo tài khoản khách hàng mới để bắt đầu đặt phòng')

@section('content')
<a href="{{ route('auth.google.redirect') }}" class="tm-google-btn tm-google-btn-lg mb-3">
    <span class="tm-google-icon">G</span>
    <span>Đăng ký với Google</span>
    <i class="bi bi-arrow-right"></i>
</a>

<div class="tm-divider">hoặc tạo bằng email</div>

<form method="POST" action="{{ route('register') }}" class="tm-auth-form">
    @csrf

    <div class="tm-field mb-3">
        <label for="name"><i class="bi bi-person me-1"></i>Họ và tên</label>
        <input
            id="name"
            type="text"
            name="name"
            value="{{ old('name') }}"
            class="form-control @error('name') is-invalid @enderror"
            placeholder="Nguyễn Văn A"
            required
            autofocus
            autocomplete="name"
        >
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

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
            placeholder="Tối thiểu 8 ký tự"
            required
            autocomplete="new-password"
        >
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="tm-field mb-4">
        <label for="password_confirmation"><i class="bi bi-shield-lock me-1"></i>Nhập lại mật khẩu</label>
        <input
            id="password_confirmation"
            type="password"
            name="password_confirmation"
            class="form-control"
            placeholder="Nhập lại mật khẩu"
            required
            autocomplete="new-password"
        >
    </div>

    <button type="submit" class="btn tm-btn-primary w-100 py-3">
        <i class="bi bi-person-plus me-2"></i>Đăng ký
    </button>

    <div class="tm-auth-switch">
        <span>Đã có tài khoản?</span>
        <a href="{{ route('login') }}">Đăng nhập</a>
    </div>
</form>
@endsection
