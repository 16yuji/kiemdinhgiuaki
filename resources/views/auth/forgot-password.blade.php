@extends('layouts.auth-bootstrap')

@section('title', 'Quên mật khẩu')
@section('subtitle', 'Nhập email để nhận liên kết đặt lại mật khẩu')

@section('content')
<div class="tm-neutral-box mb-4">
    <strong><i class="bi bi-envelope-paper me-1"></i>Khôi phục tài khoản</strong>
    <div class="small text-muted mt-1">Travel Mate sẽ gửi liên kết đặt lại mật khẩu đến email đã đăng ký.</div>
</div>

<form method="POST" action="{{ route('password.email') }}" class="tm-auth-form">
    @csrf

    <div class="tm-field mb-4">
        <label for="email"><i class="bi bi-envelope me-1"></i>Email</label>
        <input
            id="email"
            type="email"
            name="email"
            value="{{ old('email') }}"
            class="form-control"
            placeholder="you@example.com"
            required
            autofocus
        >
    </div>

    <button type="submit" class="btn tm-btn-primary w-100 py-3">
        <i class="bi bi-send me-2"></i>Gửi liên kết đặt lại mật khẩu
    </button>

    <div class="tm-auth-switch">
        <a href="{{ route('login') }}">Quay lại đăng nhập</a>
    </div>
</form>
@endsection
