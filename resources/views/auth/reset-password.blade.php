@extends('layouts.auth-bootstrap')

@section('title', 'Đặt lại mật khẩu')
@section('subtitle', 'Thiết lập mật khẩu mới cho tài khoản Travel Mate')

@section('content')
<form method="POST" action="{{ route('password.store') }}" class="tm-auth-form">
    @csrf

    <input type="hidden" name="token" value="{{ $request->route('token') }}">

    <div class="tm-field mb-3">
        <label for="email"><i class="bi bi-envelope me-1"></i>Email</label>
        <input
            id="email"
            type="email"
            name="email"
            value="{{ old('email', $request->email) }}"
            class="form-control"
            required
            autofocus
            autocomplete="username"
        >
    </div>

    <div class="tm-field mb-3">
        <label for="password"><i class="bi bi-lock me-1"></i>Mật khẩu mới</label>
        <input
            id="password"
            type="password"
            name="password"
            class="form-control"
            placeholder="Nhập mật khẩu mới"
            required
            autocomplete="new-password"
        >
    </div>

    <div class="tm-field mb-4">
        <label for="password_confirmation"><i class="bi bi-shield-lock me-1"></i>Nhập lại mật khẩu mới</label>
        <input
            id="password_confirmation"
            type="password"
            name="password_confirmation"
            class="form-control"
            placeholder="Nhập lại mật khẩu mới"
            required
            autocomplete="new-password"
        >
    </div>

    <button type="submit" class="btn tm-btn-primary w-100 py-3">
        <i class="bi bi-check2-circle me-2"></i>Cập nhật mật khẩu
    </button>
</form>
@endsection
