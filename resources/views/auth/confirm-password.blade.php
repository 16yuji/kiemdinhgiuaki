@extends('layouts.auth-bootstrap')

@section('title', 'Xác nhận mật khẩu')
@section('subtitle', 'Xác nhận lại danh tính để tiếp tục thao tác bảo mật')

@section('content')
<div class="tm-policy-box mb-4">
    <strong><i class="bi bi-shield-lock me-1"></i>Khu vực bảo mật.</strong>
    Vui lòng nhập mật khẩu hiện tại trước khi tiếp tục.
</div>

<form method="POST" action="{{ route('password.confirm') }}" class="tm-auth-form">
    @csrf

    <div class="tm-field mb-4">
        <label for="password"><i class="bi bi-lock me-1"></i>Mật khẩu</label>
        <input
            id="password"
            type="password"
            name="password"
            class="form-control"
            required
            autocomplete="current-password"
        >
    </div>

    <button type="submit" class="btn tm-btn-primary w-100 py-3">
        <i class="bi bi-check2-circle me-2"></i>Xác nhận
    </button>
</form>
@endsection
