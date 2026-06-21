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

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.hotel-appeals.index') }}" class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Từ khóa</label>
                <input
                    type="text"
                    name="keyword"
                    class="form-control"
                    value="{{ request('keyword') }}"
                    placeholder="Tên khách sạn, Owner, nội dung giải trình"
                >
            </div>

            <div class="col-md-4">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">-- Tất cả --</option>
                    <option value="pending" @selected(request('status') === 'pending')>Chờ xử lý</option>
                    <option value="approved" @selected(request('status') === 'approved')>Đã chấp nhận</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>Đã từ chối</option>
                </select>
            </div>

            <div class="col-md-2 d-flex align-items-end gap-2">
                <button class="btn btn-primary w-100">Lọc</button>
                <a href="{{ route('admin.hotel-appeals.index') }}" class="btn btn-outline-secondary">Xóa</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                <tr>
                    <th>Khách sạn</th>
                    <th>Owner</th>
                    <th>Nội dung</th>
                    <th>Trạng thái KS</th>
                    <th>Trạng thái YC</th>
                    <th>Ngày gửi</th>
                    <th class="text-end">Thao tác</th>
                </tr>
                </thead>

                <tbody>
                @forelse($appeals as $appeal)
                    <tr>
                        <td>{{ $appeal->hotel->name }}</td>

                        <td>
                            <div>{{ $appeal->owner->name }}</div>
                            <div class="text-muted small">{{ $appeal->owner->email }}</div>
                        </td>

                        <td>{{ \Illuminate\Support\Str::limit($appeal->reason, 80) }}</td>

                        <td>
                            @include('admin.hotels._status', ['status' => $appeal->hotel->status])
                        </td>

                        <td>
                            @if($appeal->status === 'pending')
                                <span class="badge bg-warning text-dark">Chờ xử lý</span>
                            @elseif($appeal->status === 'approved')
                                <span class="badge bg-success">Đã chấp nhận</span>
                            @else
                                <span class="badge bg-danger">Đã từ chối</span>
                            @endif
                        </td>

                        <td>{{ $appeal->created_at->format('d/m/Y H:i') }}</td>

                        <td class="text-end">
                            <a href="{{ route('admin.hotel-appeals.show', $appeal) }}" class="btn btn-sm btn-outline-primary">
                                Xem
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            Chưa có yêu cầu xem xét nào.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $appeals->links() }}
        </div>
    </div>
</div>
@endsection