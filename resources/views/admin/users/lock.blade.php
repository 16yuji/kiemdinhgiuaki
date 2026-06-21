@extends('layouts.dashboard')

@section('title', 'Khóa tài khoản')
@section('page-title', 'Khóa tài khoản')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card shadow-sm border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">Xác nhận khóa tài khoản</h5>
            </div>

            <div class="card-body">
                <div class="alert alert-warning">
                    Sau khi khóa, người dùng sẽ không thể đăng nhập. Nếu họ đang đăng nhập, hệ thống sẽ tự đăng xuất ở lần truy cập tiếp theo.
                </div>

                <table class="table">
                    <tr>
                        <th style="width: 180px;">Họ tên</th>
                        <td>{{ $user->name }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <th>Vai trò</th>
                        <td>{{ $user->role }}</td>
                    </tr>
                    <tr>
                        <th>Trạng thái</th>
                        <td>{{ $user->status }}</td>
                    </tr>
                </table>

                <form method="POST" action="{{ route('admin.users.lock', $user) }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">
                            Lý do khóa tài khoản <span class="text-danger">*</span>
                        </label>
                        <textarea
                            name="lock_reason"
                            rows="4"
                            class="form-control @error('lock_reason') is-invalid @enderror"
                            placeholder="Ví dụ: Tài khoản vi phạm quy định, thông tin không hợp lệ, có dấu hiệu lạm dụng..."
                            required
                        >{{ old('lock_reason') }}</textarea>

                        @error('lock_reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-danger" type="submit">
                            Xác nhận khóa
                        </button>

                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-secondary">
                            Hủy
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection