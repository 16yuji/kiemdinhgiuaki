@extends('layouts.dashboard')

@section('title', 'Đổi phòng')
@section('page-title', 'Đổi phòng')

@section('content')
@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Đổi phòng cho đơn {{ $booking->booking_code }}</h4>
        <p class="text-muted mb-0">
            Chọn phòng đang sử dụng và phòng mới cùng hạng đang sẵn sàng để đổi cho khách.
        </p>
    </div>

    <a href="{{ route('owner.bookings.show', $booking) }}" class="btn btn-outline-secondary">
        Quay lại
    </a>
</div>

<div class="row g-4">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Thông tin đơn</h5>
            </div>

            <div class="card-body">
                <table class="table">
                    <tr>
                        <th style="width: 180px;">Mã đơn</th>
                        <td>{{ $booking->booking_code }}</td>
                    </tr>

                    <tr>
                        <th>Khách sạn</th>
                        <td>{{ $booking->hotel->name }}</td>
                    </tr>

                    <tr>
                        <th>Ngày lưu trú</th>
                        <td>
                            {{ $booking->checkin_date->format('d/m/Y') }}
                            -
                            {{ $booking->checkout_date->format('d/m/Y') }}
                        </td>
                    </tr>

                    <tr>
                        <th>Người liên hệ</th>
                        <td>{{ $booking->contact_name }} - {{ $booking->contact_phone }}</td>
                    </tr>

                    <tr>
                        <th>Trạng thái</th>
                        <td>
                            <span class="badge bg-primary">Đang lưu trú</span>
                        </td>
                    </tr>
                </table>

                <div class="alert alert-info small mb-0">
                    Theo đặc tả, phòng mới phải đang ở trạng thái <strong>Sẵn sàng</strong>
                    và cùng hạng hoặc tương đương với phòng hiện tại.
                    Hệ thống hiện đang giới hạn theo <strong>cùng hạng phòng</strong> để đảm bảo an toàn dữ liệu.
                </div>
            </div>
        </div>

        <div class="card shadow-sm mt-3">
            <div class="card-header bg-white">
                <h5 class="mb-0">Phòng đang sử dụng</h5>
            </div>

            <div class="card-body">
                @forelse($currentAssignments as $assignment)
                    <div class="border rounded p-3 mb-2">
                        <div class="fw-semibold">
                            Phòng {{ $assignment->room->room_number }}

                            @if($assignment->room->floor)
                                - Tầng {{ $assignment->room->floor }}
                            @endif
                        </div>

                        <div class="text-muted small">
                            Hạng phòng: {{ $assignment->room->roomType->name ?? '-' }}
                        </div>

                        <div class="text-muted small">
                            Gán lúc:
                            {{ $assignment->assigned_at ? $assignment->assigned_at->format('d/m/Y H:i') : '-' }}
                        </div>

                        @if($assignment->note)
                            <div class="text-muted small">
                                Ghi chú: {{ $assignment->note }}
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-muted mb-0">Không có phòng đang sử dụng.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Thực hiện đổi phòng</h5>
            </div>

            <div class="card-body">
                @if($availableRooms->count())
                    <form method="POST" action="{{ route('owner.bookings.change-room.store', $booking) }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">
                                Phòng hiện tại cần đổi <span class="text-danger">*</span>
                            </label>

                            <select
                                name="assignment_id"
                                class="form-select @error('assignment_id') is-invalid @enderror"
                                required
                            >
                                <option value="">-- Chọn phòng hiện tại --</option>

                                @foreach($currentAssignments as $assignment)
                                    <option value="{{ $assignment->id }}" @selected(old('assignment_id') == $assignment->id)>
                                        Phòng {{ $assignment->room->room_number }}
                                        - {{ $assignment->room->roomType->name ?? 'Không rõ hạng' }}
                                    </option>
                                @endforeach
                            </select>

                            @error('assignment_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Phòng mới <span class="text-danger">*</span>
                            </label>

                            <select
                                name="new_room_id"
                                class="form-select @error('new_room_id') is-invalid @enderror"
                                required
                            >
                                <option value="">-- Chọn phòng mới --</option>

                                @foreach($availableRooms as $room)
                                    <option value="{{ $room->id }}" @selected(old('new_room_id') == $room->id)>
                                        Phòng {{ $room->room_number }}
                                        @if($room->floor)
                                            - Tầng {{ $room->floor }}
                                        @endif
                                        - {{ $room->roomType->name ?? 'Không rõ hạng' }}
                                    </option>
                                @endforeach
                            </select>

                            @error('new_room_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <div class="form-text">
                                Chỉ hiển thị các phòng cùng hạng đang ở trạng thái sẵn sàng.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Trạng thái phòng cũ sau khi đổi <span class="text-danger">*</span>
                            </label>

                            <select
                                name="old_room_next_status"
                                class="form-select @error('old_room_next_status') is-invalid @enderror"
                                required
                            >
                                <option value="cleaning" @selected(old('old_room_next_status') === 'cleaning')>
                                    Đang dọn
                                </option>

                                <option value="maintenance" @selected(old('old_room_next_status') === 'maintenance')>
                                    Bảo trì
                                </option>
                            </select>

                            @error('old_room_next_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <div class="form-text">
                                Nếu phòng cũ chỉ cần vệ sinh, chọn Đang dọn.
                                Nếu phòng cũ có sự cố, chọn Bảo trì.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Lý do đổi phòng <span class="text-danger">*</span>
                            </label>

                            <textarea
                                name="change_reason"
                                rows="4"
                                class="form-control @error('change_reason') is-invalid @enderror"
                                placeholder="Ví dụ: Phòng cũ bị lỗi điều hòa, khách yêu cầu đổi phòng..."
                                required
                            >{{ old('change_reason') }}</textarea>

                            @error('change_reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button
                            type="submit"
                            class="btn btn-warning w-100"
                            onclick="return confirm('Xác nhận đổi phòng cho khách?')"
                        >
                            Xác nhận đổi phòng
                        </button>
                    </form>
                @else
                    <div class="alert alert-warning mb-0">
                        Hiện không có phòng cùng hạng đang sẵn sàng để đổi.
                        Vui lòng kiểm tra trạng thái phòng hoặc xử lý thủ công.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection