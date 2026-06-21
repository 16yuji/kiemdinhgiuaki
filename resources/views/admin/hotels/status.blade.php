@extends('layouts.dashboard')

@section('title', 'Đổi trạng thái khách sạn')
@section('page-title', 'Đổi trạng thái khách sạn')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">Xác nhận thay đổi trạng thái cơ sở lưu trú</h5>
            </div>

            <div class="card-body">
                <div class="alert alert-warning">
                    Khách sạn bị ẩn hoặc khóa sẽ không xuất hiện trên trang tìm kiếm và không thể tạo đơn đặt phòng mới.
                    Dữ liệu cũ vẫn được giữ lại để theo dõi, xử lý đơn và đối soát.
                </div>

                <table class="table">
                    <tr>
                        <th style="width: 180px;">Khách sạn</th>
                        <td>{{ $hotel->name }}</td>
                    </tr>
                    <tr>
                        <th>Owner</th>
                        <td>{{ $hotel->owner->name ?? '-' }} - {{ $hotel->owner->email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Trạng thái hiện tại</th>
                        <td>@include('admin.hotels._status', ['status' => $hotel->status])</td>
                    </tr>
                    <tr>
                        <th>Lý do hiện tại</th>
                        <td>{{ $hotel->status_reason ?: '-' }}</td>
                    </tr>
                </table>

                <form method="POST" action="{{ route('admin.hotels.status.update', $hotel) }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Trạng thái mới <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="active" @selected(old('status', $hotel->status) === 'active')>
                                Hoạt động
                            </option>
                            <option value="hidden" @selected(old('status', $hotel->status) === 'hidden')>
                                Ẩn khỏi kết quả tìm kiếm
                            </option>
                            <option value="locked" @selected(old('status', $hotel->status) === 'locked')>
                                Khóa cơ sở lưu trú
                            </option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Lý do xử lý
                            <span class="text-danger">* nếu ẩn/khóa</span>
                        </label>
                        <textarea
                            name="status_reason"
                            rows="4"
                            class="form-control @error('status_reason') is-invalid @enderror"
                            placeholder="Ví dụ: Thông tin sai lệch, vi phạm chính sách, ảnh không phù hợp..."
                        >{{ old('status_reason', $hotel->status_reason) }}</textarea>
                        @error('status_reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-danger" type="submit">
                            Xác nhận cập nhật
                        </button>

                        <a href="{{ route('admin.hotels.show', $hotel) }}" class="btn btn-outline-secondary">
                            Hủy
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection