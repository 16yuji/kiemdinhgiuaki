@extends('layouts.dashboard')

@section('title', 'Duyệt yêu cầu đối tác')
@section('page-title', 'Duyệt yêu cầu đối tác')

@section('content')
<div class="tm-page-toolbar">
    <div>
        <div class="tm-eyebrow"><i class="bi bi-person-check"></i> Partner onboarding</div>
        <h4 class="fw-black mb-1">Yêu cầu trở thành đối tác</h4>
        <p class="text-muted fw-semibold mb-0">Duyệt hoặc từ chối yêu cầu chuyển Customer thành Owner.</p>
    </div>
    <span class="tm-dashboard-chip"><i class="bi bi-inboxes"></i> {{ $partnerRequests->total() }} yêu cầu</span>
</div>

<div class="tm-form-card mb-4">
    <form method="GET" action="{{ route('admin.partner-requests.index') }}" class="row g-3 align-items-end">
        <div class="col-lg-6">
            <div class="tm-field">
                <label><i class="bi bi-search me-1"></i> Từ khóa</label>
                <input
                    type="text"
                    name="keyword"
                    class="form-control"
                    value="{{ request('keyword') }}"
                    placeholder="Tên cơ sở, email, số điện thoại, người gửi"
                >
            </div>
        </div>

        <div class="col-lg-4">
            <div class="tm-field">
                <label>Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả trạng thái</option>
                    <option value="pending" @selected(request('status') === 'pending')>Chờ duyệt</option>
                    <option value="approved" @selected(request('status') === 'approved')>Đã duyệt</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>Từ chối</option>
                </select>
            </div>
        </div>

        <div class="col-lg-2 d-flex gap-2">
            <button class="btn tm-btn-primary flex-fill" type="submit">
                <i class="bi bi-funnel me-1"></i> Lọc
            </button>
            <a href="{{ route('admin.partner-requests.index') }}" class="btn tm-btn-light">
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
                <th>Cơ sở</th>
                <th>Người gửi</th>
                <th>Liên hệ</th>
                <th>Trạng thái</th>
                <th>Ngày gửi</th>
                <th class="text-end">Thao tác</th>
            </tr>
            </thead>

            <tbody>
            @forelse($partnerRequests as $requestItem)
                <tr>
                    <td>
                        <div class="fw-black">{{ $requestItem->business_name }}</div>
                        <div class="text-muted small">{{ $requestItem->address }}</div>
                    </td>

                    <td>
                        <div class="fw-semibold">{{ $requestItem->user->name }}</div>
                        <div class="text-muted small">{{ $requestItem->user->email }}</div>
                    </td>

                    <td>
                        <div>{{ $requestItem->contact_phone }}</div>
                        <div class="text-muted small">{{ $requestItem->contact_email }}</div>
                    </td>

                    <td>
                        @if($requestItem->status === 'pending')
                            <span class="tm-status tm-status-warning">Chờ duyệt</span>
                        @elseif($requestItem->status === 'approved')
                            <span class="tm-status tm-status-success">Đã duyệt</span>
                        @else
                            <span class="tm-status tm-status-danger">Từ chối</span>
                        @endif
                    </td>

                    <td>{{ $requestItem->created_at->format('d/m/Y H:i') }}</td>

                    <td class="text-end">
                        <div class="tm-action-stack">
                            <a href="{{ route('admin.partner-requests.show', $requestItem) }}" class="btn btn-sm tm-btn-light">
                                <i class="bi bi-eye me-1"></i> Xem
                            </a>

                            @if($requestItem->status === 'pending')
                                <form
                                    method="POST"
                                    action="{{ route('admin.partner-requests.approve', $requestItem) }}"
                                    class="d-inline"
                                    onsubmit="return confirm('Duyệt yêu cầu này?')"
                                >
                                    @csrf
                                    <button class="btn btn-sm btn-success" type="submit">
                                        <i class="bi bi-check2 me-1"></i> Duyệt
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <div class="tm-empty-state">
                            <i class="bi bi-inbox"></i>
                            Chưa có yêu cầu đối tác nào.
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $partnerRequests->links() }}
    </div>
</div>
@endsection
