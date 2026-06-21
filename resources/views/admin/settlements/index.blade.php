@extends('layouts.dashboard')

@section('title', 'Đối soát doanh thu')
@section('page-title', 'Đối soát doanh thu')

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Danh sách giao dịch tài chính</h4>
        <p class="text-muted mb-0">
            Theo dõi doanh thu, công nợ hoàn tiền sau settlement và số tiền thực chuyển cho Owner.
        </p>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.settlements.index') }}" class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Từ khóa</label>
                <input
                    type="text"
                    name="keyword"
                    class="form-control"
                    value="{{ request('keyword') }}"
                    placeholder="Mã đơn, khách sạn, tên/email Owner"
                >
            </div>

            <div class="col-md-4">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">-- Tất cả --</option>
                    <option value="temporary_recorded" @selected(request('status') === 'temporary_recorded')>
                        Ghi nhận tạm
                    </option>
                    <option value="waiting_settlement" @selected(request('status') === 'waiting_settlement')>
                        Chờ đối soát
                    </option>
                    <option value="settled" @selected(request('status') === 'settled')>
                        Đã đối soát
                    </option>
                    <option value="adjusted" @selected(request('status') === 'adjusted')>
                        Đã điều chỉnh
                    </option>
                    <option value="postponed" @selected(request('status') === 'postponed')>
                        Tạm hoãn
                    </option>
                </select>
            </div>

            <div class="col-md-2 d-flex align-items-end gap-2">
                <button class="btn btn-primary w-100">Lọc</button>
                <a href="{{ route('admin.settlements.index') }}" class="btn btn-outline-secondary">
                    Xóa
                </a>
            </div>
        </form>
    </div>
</div>

<div class="tm-neutral-box mb-4">
    <strong>Pending deductions</strong> là các khoản OwnerAdjustment phát sinh khi Admin hoàn tiền cho khách sau khi booking cũ đã được settlement. Khi xác nhận chuyển tiền, hệ thống giữ nguyên owner_amount lịch sử và chỉ giảm số tiền thực chuyển trong bản ghi Settlement.
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Khách sạn</th>
                    <th>Owner</th>
                    <th>Tổng tiền</th>
                    <th>Phí nền tảng</th>
                    <th>Trả Owner</th>
                    <th>Chờ khấu trừ</th>
                    <th>Thực chuyển dự kiến</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
                </thead>

                <tbody>
                @forelse($transactions as $transaction)
                    @php
                        $pendingDeduction = (float) ($pendingAdjustmentsByOwner[$transaction->owner_id] ?? 0);
                        $estimatedTransfer = max(0, (float) $transaction->owner_amount - $pendingDeduction);
                    @endphp
                    <tr>
                        <td>{{ $transaction->booking->booking_code }}</td>

                        <td>
                            <div class="fw-semibold">{{ $transaction->booking->hotel->name }}</div>
                            <div class="text-muted small">
                                {{ $transaction->booking->checkin_date->format('d/m/Y') }}
                                -
                                {{ $transaction->booking->checkout_date->format('d/m/Y') }}
                            </div>
                        </td>

                        <td>
                            <div>{{ $transaction->owner->name }}</div>
                            <div class="text-muted small">{{ $transaction->owner->email }}</div>
                        </td>

                        <td>{{ number_format($transaction->gross_amount, 0, ',', '.') }}đ</td>
                        <td>{{ number_format($transaction->platform_fee, 0, ',', '.') }}đ</td>
                        <td class="fw-semibold text-primary">
                            {{ number_format($transaction->owner_amount, 0, ',', '.') }}đ
                        </td>
                        <td class="text-danger">
                            {{ $pendingDeduction > 0 ? number_format($pendingDeduction, 0, ',', '.') . 'đ' : '-' }}
                        </td>
                        <td class="fw-semibold text-success">
                            {{ number_format($estimatedTransfer, 0, ',', '.') }}đ
                        </td>

                        <td>
                            @include('admin.settlements._status', ['status' => $transaction->status])
                        </td>

                        <td class="text-end">
                            <a href="{{ route('admin.settlements.show', $transaction) }}" class="btn btn-sm btn-outline-primary">
                                Xem
                            </a>

                            @if(in_array($transaction->status, ['waiting_settlement', 'temporary_recorded'], true))
                                <a href="{{ route('admin.settlements.show', $transaction) }}" class="btn btn-sm btn-success">
                                    Đối soát
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            Chưa có giao dịch tài chính nào.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection
