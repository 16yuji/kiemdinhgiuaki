@extends('layouts.auth-bootstrap')

@section('title', 'Xác minh email')
@section('subtitle', 'Xác minh địa chỉ email để hoàn tất tài khoản')

@section('content')
<div class="tm-neutral-box mb-4">
    <strong><i class="bi bi-envelope-check me-1"></i>Kiểm tra hộp thư</strong>
    <div class="small text-muted mt-1">Travel Mate đã gửi email xác minh. Nếu chưa thấy, bạn có thể gửi lại liên kết.</div>
</div>

@if (session('status') == 'verification-link-sent')
    <div class="alert alert-success">
        <i class="bi bi-check-circle me-2"></i>Liên kết xác minh mới đã được gửi tới email của bạn.
    </div>
@endif

<form method="POST" action="{{ route('verification.send') }}" class="mb-3">
    @csrf
    <button type="submit" class="btn tm-btn-primary w-100 py-3">
        <i class="bi bi-send me-2"></i>Gửi lại email xác minh
    </button>
</form>

<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit" class="btn btn-outline-danger w-100 py-3">
        <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
    </button>
</form>
@endsection
