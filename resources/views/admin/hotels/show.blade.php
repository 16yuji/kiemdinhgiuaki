@extends('layouts.dashboard')

@section('title', 'Chi tiết khách sạn')
@section('page-title', 'Chi tiết khách sạn')

@section('content')
<div class="tm-page-toolbar">
    <div>
        <div class="tm-eyebrow"><i class="bi bi-building"></i> Hotel moderation</div>
        <h2 class="tm-heading-md mb-2">{{ $hotel->name }}</h2>
        <div class="tm-status-stack">
            @include('admin.hotels._status', ['status' => $hotel->status])
            <span class="tm-mini-badge"><i class="bi bi-star-fill text-warning"></i>{{ number_format($hotel->average_rating, 1) }} / 5</span>
            <span class="tm-mini-badge"><i class="bi bi-chat"></i>{{ $hotel->review_count }} đánh giá</span>
        </div>
    </div>

    <div class="tm-action-stack">
        <a href="{{ route('admin.hotels.status', $hotel) }}" class="btn btn-danger">
            <i class="bi bi-shield-exclamation me-1"></i> Đổi trạng thái
        </a>
        <a href="{{ route('admin.hotels.index') }}" class="btn tm-btn-light">
            <i class="bi bi-arrow-left me-1"></i> Quay lại
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-7">
        <div class="tm-card p-4 mb-4">
            <div class="tm-eyebrow"><i class="bi bi-info-circle"></i> Thông tin khách sạn</div>
            <div class="tm-meta-card">
                <div><span>Tên</span><strong>{{ $hotel->name }}</strong></div>
                <div><span>Owner</span><strong>{{ $hotel->owner->name ?? '-' }} · {{ $hotel->owner->email ?? '-' }}</strong></div>
                <div><span>Giờ nhận phòng</span><strong>{{ $hotel->checkin_time ? \Carbon\Carbon::parse($hotel->checkin_time)->format('H:i') : '-' }}</strong></div>
                <div><span>Giờ trả phòng</span><strong>{{ $hotel->checkout_time ? \Carbon\Carbon::parse($hotel->checkout_time)->format('H:i') : '-' }}</strong></div>
                <div style="grid-column:1 / -1;"><span>Địa chỉ</span><strong>{{ $hotel->address }}, {{ $hotel->ward }}, {{ $hotel->district }}, {{ $hotel->province }}</strong></div>
                <div style="grid-column:1 / -1;"><span>Mô tả</span><strong>{{ $hotel->description ?: '-' }}</strong></div>
            </div>
        </div>

        <div class="tm-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="tm-eyebrow mb-0"><i class="bi bi-door-open"></i> Hạng phòng</div>
                <span class="tm-dashboard-chip">{{ $hotel->roomTypes->count() }} hạng</span>
            </div>

            <div class="row g-3">
                @forelse($hotel->roomTypes as $roomType)
                    <div class="col-md-6">
                        <div class="tm-neutral-box h-100">
                            <strong>{{ $roomType->name }}</strong>
                            <div class="text-muted fw-semibold small mt-1">
                                {{ number_format($roomType->price_per_night, 0, ',', '.') }}đ / đêm ·
                                {{ $roomType->max_guests }} khách ·
                                {{ $roomType->rooms->count() }} phòng
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="tm-empty-state">
                            <i class="bi bi-door-open"></i>
                            Chưa có hạng phòng.
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="tm-form-card">
            <div class="tm-eyebrow"><i class="bi bi-shield-check"></i> Trạng thái kiểm duyệt</div>
            <div class="tm-info-list">
                <div><span>Trạng thái</span><strong>@include('admin.hotels._status', ['status' => $hotel->status])</strong></div>
                <div><span>Lý do</span><strong>{{ $hotel->status_reason ?: '-' }}</strong></div>
                <div><span>Thời gian cập nhật</span><strong>{{ $hotel->status_changed_at ? $hotel->status_changed_at->format('d/m/Y H:i') : '-' }}</strong></div>
                <div><span>Người xử lý</span><strong>{{ $hotel->statusChangedBy->name ?? '-' }}</strong></div>
            </div>

            <a href="{{ route('admin.hotels.status', $hotel) }}" class="btn btn-outline-danger w-100 mt-3">
                <i class="bi bi-shield-exclamation me-1"></i> Thay đổi trạng thái
            </a>
        </div>
    </div>
</div>
@endsection
