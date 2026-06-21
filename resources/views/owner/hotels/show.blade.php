@extends('layouts.dashboard')

@section('title', 'Chi tiết khách sạn')
@section('page-title', 'Chi tiết khách sạn')

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">{{ $hotel->name }}</h4>

        @if($hotel->status === 'active')
            <span class="badge bg-success">Hoạt động</span>
        @elseif($hotel->status === 'hidden')
            <span class="badge bg-warning text-dark">Đang ẩn</span>
        @else
            <span class="badge bg-danger">Bị khóa</span>
        @endif
    </div>

    <div class="d-flex gap-2">
        @if(in_array($hotel->status, ['hidden', 'locked'], true))
            <a href="{{ route('owner.hotels.appeal.create', $hotel) }}" class="btn btn-warning">
                Gửi yêu cầu xem xét
            </a>
        @endif

        <a href="{{ route('owner.hotels.edit', $hotel) }}" class="btn btn-primary">
            Sửa
        </a>

        <a href="{{ route('owner.hotels.index') }}" class="btn btn-outline-secondary">
            Quay lại
        </a>
    </div>
</div>

@if($hotel->status !== 'active')
    <div class="alert alert-warning">
        <strong>Khách sạn đang không hoạt động.</strong>
        <div>Lý do Admin xử lý: {{ $hotel->status_reason ?: 'Không có lý do cụ thể.' }}</div>
        <div>
            Thời gian xử lý:
            {{ $hotel->status_changed_at ? $hotel->status_changed_at->format('d/m/Y H:i') : '-' }}
        </div>
    </div>
@endif

<div class="row g-4">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Thông tin khách sạn</h5>
            </div>

            <div class="card-body">
                @if($hotel->thumbnail)
                    <img
                        src="{{ asset('storage/' . $hotel->thumbnail) }}"
                        alt="{{ $hotel->name }}"
                        class="img-fluid rounded mb-3"
                        style="max-height: 260px; object-fit: cover; width: 100%;"
                    >
                @endif

                <table class="table">
                    <tr>
                        <th style="width: 180px;">Tên</th>
                        <td>{{ $hotel->name }}</td>
                    </tr>

                    <tr>
                        <th>Địa chỉ</th>
                        <td>
                            {{ $hotel->address }},
                            {{ $hotel->ward }},
                            {{ $hotel->district }},
                            {{ $hotel->province }}
                        </td>
                    </tr>

                    <tr>
                        <th>Giờ nhận/trả</th>
                        <td>
                            {{ $hotel->checkin_time ? \Carbon\Carbon::parse($hotel->checkin_time)->format('H:i') : '-' }}
                            /
                            {{ $hotel->checkout_time ? \Carbon\Carbon::parse($hotel->checkout_time)->format('H:i') : '-' }}
                        </td>
                    </tr>

                    <tr>
                        <th>Mô tả</th>
                        <td>{{ $hotel->description ?: '-' }}</td>
                    </tr>

                    <tr>
                        <th>Chính sách hủy</th>
                        <td>
                            {{ $hotel->cancellation_policy ?: 'Chưa cập nhật chính sách hủy / hoàn tiền.' }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card shadow-sm mt-3">
            <div class="card-header bg-white">
                <h5 class="mb-0">Tiện nghi chung</h5>
            </div>

            <div class="card-body">
                @forelse($hotel->amenities as $amenity)
                    <span class="badge bg-light text-dark border me-1 mb-1">
                        {{ $amenity->name }}
                    </span>
                @empty
                    <p class="text-muted mb-0">Chưa chọn tiện nghi chung.</p>
                @endforelse
            </div>
        </div>

        <div class="card shadow-sm mt-3">
            <div class="card-header bg-white">
                <h5 class="mb-0">Hạng phòng</h5>
            </div>

            <div class="card-body">
                @forelse($hotel->roomTypes as $roomType)
                    <div class="border rounded p-3 mb-2">
                        <div class="fw-semibold">{{ $roomType->name }}</div>
                        <div class="text-muted small">
                            Giá: {{ number_format($roomType->price_per_night, 0, ',', '.') }}đ / đêm |
                            Sức chứa: {{ $roomType->max_guests }} |
                            Số phòng: {{ $roomType->rooms->count() }}
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">Chưa có hạng phòng.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Thư viện ảnh</h5>
            </div>

            <div class="card-body">
                <div class="row g-2">
                    @forelse($hotel->images as $image)
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <img
                                    src="{{ asset('storage/' . $image->path) }}"
                                    alt="Ảnh khách sạn"
                                    class="img-fluid rounded mb-2"
                                    style="height: 120px; width: 100%; object-fit: cover;"
                                >

                                <form
                                    method="POST"
                                    action="{{ route('owner.hotel-images.destroy', $image) }}"
                                    onsubmit="return confirm('Xóa ảnh này?')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-sm btn-outline-danger w-100" type="submit">
                                        Xóa ảnh
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <p class="text-muted mb-0">Chưa có ảnh trong thư viện.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        @if($hotel->statusAppeals->count())
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Yêu cầu xem xét gần đây</h5>
                </div>

                <div class="card-body">
                    @foreach($hotel->statusAppeals->take(3) as $appeal)
                        <div class="border rounded p-2 mb-2">
                            <div>
                                @if($appeal->status === 'pending')
                                    <span class="badge bg-warning text-dark">Chờ xử lý</span>
                                @elseif($appeal->status === 'approved')
                                    <span class="badge bg-success">Đã chấp nhận</span>
                                @else
                                    <span class="badge bg-danger">Đã từ chối</span>
                                @endif
                            </div>

                            <div class="small mt-1">
                                {{ \Illuminate\Support\Str::limit($appeal->reason, 100) }}
                            </div>

                            @if($appeal->admin_reply)
                                <div class="small text-muted mt-1">
                                    Phản hồi: {{ $appeal->admin_reply }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection