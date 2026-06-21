@extends('layouts.dashboard')

@section('title', 'Quản lý người dùng')
@section('page-title', 'Quản lý người dùng')

@section('content')
<div class="tm-page-toolbar">
    <div>
        <div class="tm-eyebrow"><i class="bi bi-people"></i> Tài khoản hệ thống</div>
        <h4 class="fw-black mb-1">Danh sách người dùng</h4>
        <p class="text-muted fw-semibold mb-0">Theo dõi Customer, Owner và Admin; khóa tài khoản khi có rủi ro vận hành.</p>
    </div>
    <span class="tm-dashboard-chip"><i class="bi bi-person-lines-fill"></i> {{ $users->total() }} tài khoản</span>
</div>

<div class="tm-form-card mb-4">
    <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3 align-items-end">
        <div class="col-lg-4">
            <div class="tm-field">
                <label><i class="bi bi-search me-1"></i> Từ khóa</label>
                <input
                    type="text"
                    name="keyword"
                    class="form-control"
                    value="{{ request('keyword') }}"
                    placeholder="Tên, email, số điện thoại"
                >
            </div>
        </div>

        <div class="col-lg-3">
            <div class="tm-field">
                <label>Vai trò</label>
                <select name="role" class="form-select">
                    <option value="">Tất cả vai trò</option>
                    <option value="admin" @selected(request('role') === 'admin')>Admin</option>
                    <option value="owner" @selected(request('role') === 'owner')>Owner</option>
                    <option value="customer" @selected(request('role') === 'customer')>Customer</option>
                </select>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="tm-field">
                <label>Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active" @selected(request('status') === 'active')>Hoạt động</option>
                    <option value="locked" @selected(request('status') === 'locked')>Bị khóa</option>
                </select>
            </div>
        </div>

        <div class="col-lg-2 d-flex gap-2">
            <button class="btn tm-btn-primary flex-fill" type="submit">
                <i class="bi bi-funnel me-1"></i> Lọc
            </button>
            <a href="{{ route('admin.users.index') }}" class="btn tm-btn-light">
                <i class="bi bi-x-lg"></i>
            </a>
        </div>
    </form>
</div>

<div class="tm-surface p-3 p-lg-4">
    <div class="table-responsive">
        <table class="table tm-table align-middle">
            <thead>
            <tr>
                <th>Người dùng</th>
                <th>Email</th>
                <th>SĐT</th>
                <th>Vai trò</th>
                <th>Trạng thái</th>
                <th>Ngày tạo</th>
                <th class="text-end">Thao tác</th>
            </tr>
            </thead>

            <tbody>
            @forelse($users as $user)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="tm-user-avatar">{{ mb_substr($user->name, 0, 1) }}</div>
                            <div>
                                <div class="fw-black">{{ $user->name }}</div>
                                <div class="text-muted small">ID: {{ $user->id }}</div>
                            </div>
                        </div>
                    </td>

                    <td>{{ $user->email }}</td>
                    <td>{{ $user->phone ?: '-' }}</td>

                    <td>
                        @if($user->role === 'admin')
                            <span class="tm-status tm-status-danger">Admin</span>
                        @elseif($user->role === 'owner')
                            <span class="tm-status tm-status-primary">Owner</span>
                        @else
                            <span class="tm-status tm-status-muted">Customer</span>
                        @endif
                    </td>

                    <td>
                        @if($user->status === 'active')
                            <span class="tm-status tm-status-success">Hoạt động</span>
                        @else
                            <span class="tm-status tm-status-dark">Bị khóa</span>
                        @endif
                    </td>

                    <td>{{ $user->created_at->format('d/m/Y') }}</td>

                    <td class="text-end">
                        <div class="tm-action-stack">
                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm tm-btn-light">
                                <i class="bi bi-eye me-1"></i> Xem
                            </a>

                            @if($user->status === 'active')
                                <a href="{{ route('admin.users.lock.confirm', $user) }}" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-lock me-1"></i> Khóa
                                </a>
                            @else
                                <form
                                    method="POST"
                                    action="{{ route('admin.users.unlock', $user) }}"
                                    class="d-inline"
                                    onsubmit="return confirm('Mở khóa tài khoản này?')"
                                >
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success" type="submit">
                                        <i class="bi bi-unlock me-1"></i> Mở khóa
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <div class="tm-empty-state">
                            <i class="bi bi-person-x"></i>
                            Không có người dùng nào phù hợp.
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="tm-pagination-wrap mt-3">
        {{ $users->links() }}
    </div>
</div>
@endsection
