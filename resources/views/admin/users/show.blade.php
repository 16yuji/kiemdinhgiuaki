@extends('layouts.dashboard')

@section('title', 'Chi tiết người dùng')
@section('page-title', 'Chi tiết người dùng')

@section('content')
<div class="tm-page-toolbar">
    <div class="d-flex align-items-center gap-3">
        <div class="tm-user-avatar" style="width:64px;height:64px;border-radius:22px;font-size:26px;">{{ mb_substr($user->name, 0, 1) }}</div>
        <div>
            <div class="tm-eyebrow mb-1"><i class="bi bi-person"></i> User profile</div>
            <h2 class="tm-heading-md mb-1">{{ $user->name }}</h2>
            <p class="text-muted fw-semibold mb-0">{{ $user->email }}</p>
        </div>
    </div>

    <a href="{{ route('admin.users.index') }}" class="btn tm-btn-light px-4">
        <i class="bi bi-arrow-left me-1"></i> Quay lại
    </a>
</div>

<div class="row g-4">
    <div class="col-xl-7">
        <div class="tm-card p-4">
            <div class="tm-eyebrow"><i class="bi bi-card-list"></i> Thông tin tài khoản</div>
            <div class="tm-meta-card">
                <div><span>Họ tên</span><strong>{{ $user->name }}</strong></div>
                <div><span>Email</span><strong>{{ $user->email }}</strong></div>
                <div><span>Số điện thoại</span><strong>{{ $user->phone ?: '-' }}</strong></div>
                <div><span>Vai trò</span><strong>{{ ucfirst($user->role) }}</strong></div>
                <div><span>Trạng thái</span><strong>{{ $user->status === 'active' ? 'Hoạt động' : 'Bị khóa' }}</strong></div>
                <div><span>Ngày tạo</span><strong>{{ $user->created_at->format('d/m/Y H:i') }}</strong></div>
            </div>

            @if($user->status === 'locked')
                <div class="tm-danger-box mt-3">
                    <strong>Lý do khóa:</strong> {{ $user->locked_reason ?: '-' }}
                    <div class="small fw-semibold mt-2">
                        Khóa lúc {{ $user->locked_at ? $user->locked_at->format('d/m/Y H:i') : '-' }}
                        bởi {{ $user->lockedBy->name ?? '-' }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="col-xl-5">
        <div class="tm-kpi-grid mb-4" style="grid-template-columns:repeat(2,minmax(0,1fr));">
            <div class="tm-kpi-card">
                <span>Khách sạn sở hữu</span>
                <strong>{{ $user->hotels->count() }}</strong>
            </div>
            <div class="tm-kpi-card">
                <span>Đơn đặt phòng</span>
                <strong>{{ $user->bookings->count() }}</strong>
            </div>
            <div class="tm-kpi-card">
                <span>Yêu cầu đối tác</span>
                <strong>{{ $user->partnerRequests->count() }}</strong>
            </div>
            <div class="tm-kpi-card">
                <span>Đánh giá</span>
                <strong>{{ $user->reviews->count() }}</strong>
            </div>
        </div>

        <div class="tm-form-card">
            <div class="tm-eyebrow"><i class="bi bi-shield-exclamation"></i> Thao tác tài khoản</div>
            @if($user->status === 'active')
                <a href="{{ route('admin.users.lock.confirm', $user) }}" class="btn btn-outline-danger w-100">
                    <i class="bi bi-lock me-1"></i> Khóa tài khoản
                </a>
            @else
                <form
                    method="POST"
                    action="{{ route('admin.users.unlock', $user) }}"
                    onsubmit="return confirm('Mở khóa tài khoản này?')"
                >
                    @csrf
                    <button class="btn btn-outline-success w-100" type="submit">
                        <i class="bi bi-unlock me-1"></i> Mở khóa tài khoản
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
