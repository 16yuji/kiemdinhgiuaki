@extends('layouts.dashboard')

@section('title', 'Check-in đơn đặt phòng')
@section('page-title', 'Check-in đơn đặt phòng')

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

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Check-in đơn {{ $booking->booking_code }}</h4>
        <p class="text-muted mb-0">
            Chọn phòng cụ thể để gán cho khách khi nhận phòng.
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
                <h5 class="mb-0">Thông tin đơn đặt phòng</h5>
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
                        <th>Số khách</th>
                        <td>{{ $booking->guest_count }}</td>
                    </tr>

                    <tr>
                        <th>Người liên hệ</th>
                        <td>{{ $booking->contact_name }}</td>
                    </tr>

                    <tr>
                        <th>Số điện thoại</th>
                        <td>{{ $booking->contact_phone }}</td>
                    </tr>

                    <tr>
                        <th>Email</th>
                        <td>{{ $booking->contact_email ?: '-' }}</td>
                    </tr>

                    <tr>
                        <th>Yêu cầu đặc biệt</th>
                        <td>{{ $booking->special_request ?: '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card shadow-sm mt-3">
            <div class="card-header bg-white">
                <h5 class="mb-0">Hạng phòng đã đặt</h5>
            </div>

            <div class="card-body">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Hạng phòng</th>
                        <th>Số lượng</th>
                        <th>Giá/đêm</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($booking->roomTypes as $item)
                        <tr>
                            <td>{{ $item->roomType->name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format($item->price_per_night, 0, ',', '.') }}đ</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <div class="alert alert-info small mb-0">
                    Bạn cần chọn đúng số lượng phòng theo từng hạng phòng mà khách đã đặt.
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Chọn phòng check-in</h5>
            </div>

            <div class="card-body">
                @if($rooms->count())
                    <form method="POST" action="{{ route('owner.bookings.check-in.store', $booking) }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Phòng sẵn sàng</label>

                            @foreach($rooms as $room)
                                <label class="border rounded p-2 d-block mb-2">
                                    <input
                                        type="checkbox"
                                        name="room_ids[]"
                                        value="{{ $room->id }}"
                                        class="form-check-input me-2"
                                        @checked(in_array($room->id, old('room_ids', [])))
                                    >

                                    <strong>Phòng {{ $room->room_number }}</strong>

                                    @if($room->floor)
                                        - Tầng {{ $room->floor }}
                                    @endif

                                    <div class="text-muted small">
                                        Hạng phòng: {{ $room->roomType->name ?? '-' }}
                                    </div>
                                </label>
                            @endforeach

                            @error('room_ids')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror

                            @error('room_ids.*')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ghi chú check-in</label>
                            <textarea
                                name="checkin_note"
                                rows="3"
                                class="form-control @error('checkin_note') is-invalid @enderror"
                                placeholder="Ví dụ: Khách đến đúng giờ, đã xác minh giấy tờ, yêu cầu thêm giường phụ..."
                            >{{ old('checkin_note') }}</textarea>

                            @error('checkin_note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button
                            type="submit"
                            class="btn btn-success w-100"
                            onclick="return confirm('Xác nhận check-in và gán các phòng đã chọn cho khách?')"
                        >
                            Xác nhận check-in
                        </button>
                    </form>
                @else
                    <div class="alert alert-warning mb-0">
                        Hiện không có phòng nào ở trạng thái sẵn sàng cho đơn này.
                        Đơn có thể cần chuyển sang trạng thái xử lý thủ công.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection