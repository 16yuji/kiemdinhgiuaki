@extends('layouts.dashboard')

@section('title', 'Chi tiết hạng phòng')
@section('page-title', 'Chi tiết hạng phòng')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">{{ $roomType->name }}</h4>
        <p class="text-muted mb-0">{{ $roomType->hotel->name }}</p>
    </div>

    <div>
        <a href="{{ route('owner.room-types.edit', $roomType) }}" class="btn btn-primary">
            Sửa thông tin
        </a>
        <a href="{{ route('owner.room-types.index') }}" class="btn btn-outline-secondary">
            Quay lại
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body">
                @if($roomType->thumbnail)
                    <img
                        src="{{ asset('storage/' . $roomType->thumbnail) }}"
                        alt="{{ $roomType->name }}"
                        class="img-fluid rounded"
                    >
                @else
                    <div class="bg-light rounded d-flex align-items-center justify-content-center"
                         style="height: 260px;">
                        <span class="text-muted">Chưa có ảnh đại diện</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5>Thông tin hạng phòng</h5>

                <table class="table">
                    <tr>
                        <th style="width: 180px;">Tên hạng phòng</th>
                        <td>{{ $roomType->name }}</td>
                    </tr>
                    <tr>
                        <th>Khách sạn</th>
                        <td>{{ $roomType->hotel->name }}</td>
                    </tr>
                    <tr>
                        <th>Giá một đêm</th>
                        <td>{{ number_format($roomType->price_per_night, 0, ',', '.') }}đ</td>
                    </tr>
                    <tr>
                        <th>Sức chứa</th>
                        <td>{{ $roomType->max_guests }} khách</td>
                    </tr>
                    <tr>
                        <th>Loại giường</th>
                        <td>{{ $roomType->bed_type ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Diện tích</th>
                        <td>{{ $roomType->area ? $roomType->area . ' m²' : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Số phòng cụ thể</th>
                        <td>{{ $roomType->rooms->count() }} phòng</td>
                    </tr>
                    <tr>
                        <th>Trạng thái</th>
                        <td>
                            @if($roomType->status === 'active')
                                <span class="badge bg-success">Đang bán</span>
                            @else
                                <span class="badge bg-secondary">Tạm ẩn</span>
                            @endif
                        </td>
                    </tr>
                </table>

                <h5 class="mt-4">Tiện nghi</h5>
                @forelse($roomType->amenities as $amenity)
                    <span class="badge bg-light text-dark border me-1 mb-1">
                        {{ $amenity->name }}
                    </span>
                @empty
                    <p class="text-muted">Chưa có tiện nghi.</p>
                @endforelse

                <h5 class="mt-4">Mô tả</h5>
                <p class="text-muted">
                    {{ $roomType->description ?: 'Chưa có mô tả.' }}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection