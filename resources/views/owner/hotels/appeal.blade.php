@extends('layouts.dashboard')

@section('title', 'Yêu cầu xem xét khách sạn')
@section('page-title', 'Yêu cầu xem xét khách sạn')

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Gửi yêu cầu xem xét lại trạng thái</h5>
            </div>

            <div class="card-body">
                <div class="alert alert-warning">
                    Khách sạn của bạn đang ở trạng thái không hoạt động trên hệ thống.
                    Bạn có thể gửi giải trình để Admin xem xét khôi phục.
                </div>

                <table class="table">
                    <tr>
                        <th style="width: 180px;">Khách sạn</th>
                        <td>{{ $hotel->name }}</td>
                    </tr>
                    <tr>
                        <th>Trạng thái hiện tại</th>
                        <td>
                            @if($hotel->status === 'hidden')
                                <span class="badge bg-warning text-dark">Đang ẩn</span>
                            @elseif($hotel->status === 'locked')
                                <span class="badge bg-danger">Bị khóa</span>
                            @else
                                <span class="badge bg-success">Hoạt động</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Lý do từ Admin</th>
                        <td>{{ $hotel->status_reason ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Thời gian xử lý</th>
                        <td>{{ $hotel->status_changed_at ? $hotel->status_changed_at->format('d/m/Y H:i') : '-' }}</td>
                    </tr>
                </table>

                @if($latestAppeal)
                    <div class="alert alert-info">
                        <div><strong>Yêu cầu gần nhất:</strong></div>
                        <div><strong>Trạng thái:</strong>
                            @if($latestAppeal->status === 'pending')
                                <span class="badge bg-warning text-dark">Chờ xử lý</span>
                            @elseif($latestAppeal->status === 'approved')
                                <span class="badge bg-success">Đã chấp nhận</span>
                            @else
                                <span class="badge bg-danger">Đã từ chối</span>
                            @endif
                        </div>

                        <div><strong>Nội dung:</strong> {{ $latestAppeal->reason }}</div>

                        @if($latestAppeal->admin_reply)
                            <div><strong>Phản hồi Admin:</strong> {{ $latestAppeal->admin_reply }}</div>
                        @endif
                    </div>
                @endif

                @if(!$latestAppeal || $latestAppeal->status !== 'pending')
                    <form method="POST" action="{{ route('owner.hotels.appeal.store', $hotel) }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Nội dung giải trình <span class="text-danger">*</span></label>
                            <textarea
                                name="reason"
                                rows="5"
                                class="form-control @error('reason') is-invalid @enderror"
                                placeholder="Ví dụ: Tôi đã cập nhật lại thông tin khách sạn, bổ sung ảnh thật, điều chỉnh mô tả..."
                                required
                            >{{ old('reason') }}</textarea>

                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button class="btn btn-primary" type="submit">
                            Gửi yêu cầu xem xét
                        </button>

                        <a href="{{ route('owner.hotels.show', $hotel) }}" class="btn btn-outline-secondary">
                            Quay lại
                        </a>
                    </form>
                @else
                    <a href="{{ route('owner.hotels.show', $hotel) }}" class="btn btn-outline-secondary">
                        Quay lại
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection