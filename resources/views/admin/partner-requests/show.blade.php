@extends('layouts.dashboard')

@section('title', 'Chi tiết yêu cầu đối tác')
@section('page-title', 'Chi tiết yêu cầu đối tác')

@section('content')
<div class="tm-page-toolbar">
    <div>
        <div class="tm-eyebrow"><i class="bi bi-person-check"></i> Partner request</div>
        <h2 class="tm-heading-md mb-2">{{ $partnerRequest->business_name }}</h2>
        <div class="tm-status-stack">
            @if($partnerRequest->status === 'pending')
                <span class="tm-status tm-status-warning">Chờ duyệt</span>
            @elseif($partnerRequest->status === 'approved')
                <span class="tm-status tm-status-success">Đã duyệt</span>
            @else
                <span class="tm-status tm-status-danger">Từ chối</span>
            @endif
            <span class="tm-mini-badge"><i class="bi bi-person"></i>{{ $partnerRequest->user->name }}</span>
        </div>
    </div>

    <a href="{{ route('admin.partner-requests.index') }}" class="btn tm-btn-light px-4">
        <i class="bi bi-arrow-left me-1"></i> Quay lại
    </a>
</div>

<div class="row g-4">
    <div class="col-xl-7">
        <div class="tm-card p-4">
            <div class="tm-eyebrow"><i class="bi bi-building"></i> Thông tin cơ sở</div>
            <div class="tm-meta-card">
                <div><span>Tên cơ sở</span><strong>{{ $partnerRequest->business_name }}</strong></div>
                <div><span>Ngày gửi</span><strong>{{ $partnerRequest->created_at->format('d/m/Y H:i') }}</strong></div>
                <div><span>Số điện thoại</span><strong>{{ $partnerRequest->contact_phone }}</strong></div>
                <div><span>Email liên hệ</span><strong>{{ $partnerRequest->contact_email }}</strong></div>
                <div style="grid-column:1 / -1;"><span>Địa chỉ</span><strong>{{ $partnerRequest->address }}</strong></div>
                <div style="grid-column:1 / -1;"><span>Mô tả</span><strong>{{ $partnerRequest->description ?: '-' }}</strong></div>
            </div>

            @if($partnerRequest->status === 'rejected')
                <div class="tm-danger-box mt-3">
                    <strong>Lý do từ chối:</strong> {{ $partnerRequest->reject_reason }}
                </div>
            @endif

            @if($partnerRequest->reviewer)
                <div class="tm-soft-panel mt-3">
                    Xử lý bởi <strong>{{ $partnerRequest->reviewer->name }}</strong>
                    lúc {{ $partnerRequest->reviewed_at?->format('d/m/Y H:i') }}
                </div>
            @endif
        </div>
    </div>

    <div class="col-xl-5">
        <div class="tm-form-card mb-4">
            <div class="tm-eyebrow"><i class="bi bi-person-vcard"></i> Người gửi</div>
            <div class="tm-info-list">
                <div><span>Họ tên</span><strong>{{ $partnerRequest->user->name }}</strong></div>
                <div><span>Email</span><strong>{{ $partnerRequest->user->email }}</strong></div>
                <div><span>SĐT</span><strong>{{ $partnerRequest->user->phone ?: '-' }}</strong></div>
                <div><span>Vai trò hiện tại</span><strong>{{ ucfirst($partnerRequest->user->role) }}</strong></div>
                <div><span>Trạng thái</span><strong>{{ $partnerRequest->user->status }}</strong></div>
            </div>
        </div>

        @if($partnerRequest->status === 'pending')
            <div class="tm-form-card">
                <div class="tm-eyebrow"><i class="bi bi-check2-circle"></i> Xử lý yêu cầu</div>
                <form
                    method="POST"
                    action="{{ route('admin.partner-requests.approve', $partnerRequest) }}"
                    onsubmit="return confirm('Duyệt yêu cầu này và chuyển người dùng thành Owner?')"
                >
                    @csrf
                    <button class="btn btn-success w-100 mb-3" type="submit">
                        <i class="bi bi-check2 me-1"></i> Duyệt yêu cầu
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.partner-requests.reject', $partnerRequest) }}">
                    @csrf

                    <div class="tm-field mb-3">
                        <label>Lý do từ chối <span class="text-danger">*</span></label>
                        <textarea
                            name="reject_reason"
                            class="form-control @error('reject_reason') is-invalid @enderror"
                            rows="4"
                            required
                            placeholder="Nhập lý do từ chối yêu cầu"
                        >{{ old('reject_reason') }}</textarea>

                        @error('reject_reason')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <button class="btn btn-outline-danger w-100" type="submit">
                        <i class="bi bi-x-circle me-1"></i> Từ chối yêu cầu
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
