@extends('layouts.auth-bootstrap')

@section('title', 'Tài khoản bị khóa')
@section('subtitle', 'Tài khoản hiện không thể sử dụng các chức năng chính')

@section('content')
<div class="tm-danger-box mb-4">
    <strong><i class="bi bi-lock-fill me-1"></i>Tài khoản đã bị khóa.</strong>
    Bạn cần liên hệ Admin Travel Mate để được kiểm tra và mở khóa nếu có nhầm lẫn.
</div>

<div class="tm-info-list mb-4">
    <div><span>Họ tên</span><strong>{{ $user->name }}</strong></div>
    <div><span>Email</span><strong>{{ $user->email }}</strong></div>
    <div><span>Lý do khóa</span><strong>{{ $user->locked_reason ?: 'Không có lý do cụ thể.' }}</strong></div>
    <div><span>Thời gian khóa</span><strong>{{ $user->locked_at ? $user->locked_at->format('d/m/Y H:i') : '-' }}</strong></div>
    <div><span>Người xử lý</span><strong>{{ $user->lockedBy->name ?? 'Quản trị viên' }}</strong></div>
</div>

<div class="tm-neutral-box mb-4">
    <strong>Thông tin hỗ trợ</strong>
    <div class="small text-muted mt-2">
        Hotline: <strong>1900 9999</strong><br>
        Email: <strong>support@travelmate.local</strong><br>
        Khi liên hệ, vui lòng cung cấp email tài khoản và lý do yêu cầu mở khóa.
    </div>
</div>

<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button class="btn btn-outline-danger w-100 py-3" type="submit">
        <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
    </button>
</form>
@endsection
