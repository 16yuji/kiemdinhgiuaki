@extends('layouts.dashboard')

@section('title', 'Quản lý khách sạn')
@section('page-title', 'Quản lý khách sạn')

@section('content')
<div class="tm-page-toolbar">
    <div>
        <div class="tm-eyebrow"><i class="bi bi-buildings"></i> Kiểm soát cơ sở lưu trú</div>
        <h4 class="fw-black mb-1">Danh sách khách sạn</h4>
        <p class="text-muted fw-semibold mb-0">Admin kiểm soát trạng thái hoạt động, ẩn hoặc khóa khách sạn vi phạm.</p>
    </div>
    <span class="tm-dashboard-chip"><i class="bi bi-shield-check"></i> Moderation</span>
</div>

<div class="tm-form-card mb-4">
    <form method="GET" action="{{ route('admin.hotels.index') }}" class="row g-3 align-items-end">
        <div class="col-lg-6">
            <div class="tm-field">
                <label><i class="bi bi-search me-1"></i> Từ khóa</label>
                <input
                    type="text"
                    name="keyword"
                    class="form-control"
                    value="{{ request('keyword') }}"
                    placeholder="Tên khách sạn, địa chỉ, Owner"
                >
            </div>
        </div>

        <div class="col-lg-4">
            <div class="tm-field">
                <label>Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active" @selected(request('status') === 'active')>Hoạt động</option>
                    <option value="hidden" @selected(request('status') === 'hidden')>Đang ẩn</option>
                    <option value="locked" @selected(request('status') === 'locked')>Bị khóa</option>
                </select>
            </div>
        </div>

        <div class="col-lg-2 d-flex gap-2">
            <button class="btn tm-btn-primary flex-fill" type="submit">
                <i class="bi bi-funnel me-1"></i> Lọc
            </button>
            <a href="{{ route('admin.hotels.index') }}" class="btn tm-btn-light">
                <i class="bi bi-x-lg"></i>
            </a>
        </div>
    </form>
</div>

<div class="tm-surface p-3 p-lg-4">
    <div class="table-responsive">
        <table class="table tm-table align-middle">
            <thead>
            <tr>
                <th>Khách sạn</th>
                <th>Owner</th>
                <th>Địa chỉ</th>
                <th>Đánh giá</th>
                <th>Trạng thái</th>
                <th class="text-end">Thao tác</th>
            </tr>
            </thead>

            <tbody>
            @forelse($hotels as $hotel)
                <tr>
                    <td>
                        <div class="fw-black">{{ $hotel->name }}</div>
                        <div class="text-muted small">ID: {{ $hotel->id }}</div>
                    </td>

                    <td>
                        <div class="fw-semibold">{{ $hotel->owner->name ?? '-' }}</div>
                        <div class="text-muted small">{{ $hotel->owner->email ?? '-' }}</div>
                    </td>

                    <td>
                        <div>{{ $hotel->address }}</div>
                        <div class="text-muted small">{{ $hotel->district }}, {{ $hotel->province }}</div>
                    </td>

                    <td>
                        <strong><i class="bi bi-star-fill text-warning me-1"></i>{{ number_format($hotel->average_rating, 1) }}</strong>
                        <span class="text-muted small">({{ $hotel->review_count }})</span>
                    </td>

                    <td>
                        @include('admin.hotels._status', ['status' => $hotel->status])
                    </td>

                    <td class="text-end">
                        <div class="tm-action-stack">
                            <a href="{{ route('admin.hotels.show', $hotel) }}" class="btn btn-sm tm-btn-light">
                                <i class="bi bi-eye me-1"></i> Xem
                            </a>
                            <a href="{{ route('admin.hotels.status', $hotel) }}" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-shield-exclamation me-1"></i> Trạng thái
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <div class="tm-empty-state">
                            <i class="bi bi-building-slash"></i>
                            Chưa có khách sạn nào.
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $hotels->links() }}
    </div>
</div>
@endsection
