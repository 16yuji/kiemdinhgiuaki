@extends('layouts.dashboard')

@section('title', 'Chi tiết phòng')
@section('page-title', 'Chi tiết phòng')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Phòng {{ $room->room_number }}</h4>
        <p class="text-muted mb-0">
            {{ $room->hotel->name }} - {{ $room->roomType->name }}
        </p>
    </div>

    <div>
        <a href="{{ route('owner.rooms.edit', $room) }}" class="btn btn-primary">
            Sửa thông tin
        </a>
        <a href="{{ route('owner.rooms.index') }}" class="btn btn-outline-secondary">
            Quay lại
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <table class="table">
            <tr>
                <th style="width: 180px;">Số phòng</th>
                <td>{{ $room->room_number }}</td>
            </tr>

            <tr>
                <th>Khách sạn</th>
                <td>{{ $room->hotel->name }}</td>
            </tr>

            <tr>
                <th>Hạng phòng</th>
                <td>{{ $room->roomType->name }}</td>
            </tr>

            <tr>
                <th>Tầng</th>
                <td>{{ $room->floor ?: '-' }}</td>
            </tr>

            <tr>
                <th>Trạng thái</th>
                <td>
                    @switch($room->status)
                        @case('available')
                            <span class="badge bg-success">Sẵn sàng</span>
                            @break

                        @case('occupied')
                            <span class="badge bg-primary">Đang sử dụng</span>
                            @break

                        @case('cleaning')
                            <span class="badge bg-warning text-dark">Đang dọn</span>
                            @break

                        @case('maintenance')
                            <span class="badge bg-danger">Bảo trì</span>
                            @break

                        @case('locked')
                            <span class="badge bg-secondary">Tạm khóa</span>
                            @break

                        @default
                            <span class="badge bg-light text-dark">Không rõ</span>
                    @endswitch
                </td>
            </tr>

            <tr>
                <th>Ghi chú</th>
                <td>{{ $room->note ?: 'Không có ghi chú.' }}</td>
            </tr>
        </table>
    </div>
</div>
@endsection