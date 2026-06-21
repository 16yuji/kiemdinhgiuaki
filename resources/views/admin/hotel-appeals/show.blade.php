@extends('layouts.dashboard')

@section('title', 'Chi tiết yêu cầu xem xét')
@section('page-title', 'Chi tiết yêu cầu xem xét')

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">{{ $appeal->hotel->name }}</h4>
        <p class="text-muted mb-0">Owner: {{ $appeal->owner->name }}</p>
    </div>

    <a href="{{ route('admin.hotel-appeals.index') }}" class="btn btn-outline-secondary">
        Quay lại
    </a>
</div>

<div class="row g-4">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Thông tin yêu cầu</h5>
            </div>

            <div class="card-body">
                <p>
                    <strong>Trạng thái khách sạn:</strong>
                    @include('admin.hotels._status', ['status' => $appeal->hotel->status])
                </p>

                <p><strong>Lý do Admin đã xử lý trước đó:</strong> {{ $appeal->hotel->status_reason ?: '-' }}</p>

                <hr>

                <p><strong>Nội dung giải trình của Owner:</strong></p>
                <div class="border rounded p-3 bg-light">
                    {{ $appeal->reason }}
                </div>

                <hr>

                <p><strong>Trạng thái yêu cầu:</strong>
                    @if($appeal->status === 'pending')
                        <span class="badge bg-warning text-dark">Chờ xử lý</span>
                    @elseif($appeal->status === 'approved')
                        <span class="badge bg-success">Đã chấp nhận</span>
                    @else
                        <span class="badge bg-danger">Đã từ chối</span>
                    @endif
                </p>

                @if($appeal->admin_reply)
                    <p><strong>Phản hồi Admin:</strong></p>
                    <div class="border rounded p-3">
                        {{ $appeal->admin_reply }}
                    </div>
                @endif

                @if($appeal->reviewer)
                    <div class="alert alert-info mt-3 mb-0">
                        Đã xử lý bởi {{ $appeal->reviewer->name }}
                        lúc {{ $appeal->reviewed_at?->format('d/m/Y H:i') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Thông tin khách sạn</h5>
            </div>

            <div class="card-body">
                <p><strong>Tên:</strong> {{ $appeal->hotel->name }}</p>
                <p><strong>Địa chỉ:</strong> {{ $appeal->hotel->address }}, {{ $appeal->hotel->district }}, {{ $appeal->hotel->province }}</p>
                <p><strong>Owner:</strong> {{ $appeal->owner->name }} - {{ $appeal->owner->email }}</p>

                <a href="{{ route('admin.hotels.show', $appeal->hotel) }}" class="btn btn-outline-primary w-100">
                    Xem khách sạn
                </a>
            </div>
        </div>

        @if($appeal->status === 'pending')
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Xử lý yêu cầu</h5>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('admin.hotel-appeals.approve', $appeal) }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Phản hồi khi chấp nhận</label>
                            <textarea
                                name="admin_reply"
                                rows="3"
                                class="form-control"
                                placeholder="Ví dụ: Khách sạn đã được khôi phục sau khi Owner bổ sung thông tin."
                            >{{ old('admin_reply') }}</textarea>
                        </div>

                        <button class="btn btn-success w-100" onclick="return confirm('Chấp nhận yêu cầu và khôi phục khách sạn?')">
                            Chấp nhận & mở lại khách sạn
                        </button>
                    </form>

                    <hr>

                    <form method="POST" action="{{ route('admin.hotel-appeals.reject', $appeal) }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Lý do từ chối <span class="text-danger">*</span></label>
                            <textarea
                                name="admin_reply"
                                rows="3"
                                class="form-control @error('admin_reply') is-invalid @enderror"
                                placeholder="Nhập lý do từ chối yêu cầu"
                                required
                            >{{ old('admin_reply') }}</textarea>

                            @error('admin_reply')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button class="btn btn-outline-danger w-100" onclick="return confirm('Từ chối yêu cầu này?')">
                            Từ chối yêu cầu
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection