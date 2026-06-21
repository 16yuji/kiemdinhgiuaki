@extends('layouts.dashboard')

@section('title', 'Xử lý hoàn tiền')
@section('page-title', 'Xử lý hoàn tiền')

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.refunds.index') }}" class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Từ khóa</label>
                <input
                    type="text"
                    name="keyword"
                    class="form-control"
                    value="{{ request('keyword') }}"
                    placeholder="Mã đơn, khách hàng, khách sạn, mã giao dịch"
                >
            </div>

            <div class="col-md-4">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">-- Tất cả yêu cầu hoàn tiền --</option>
                    <option value="refunding" @selected(request('status') === 'refunding')>Chờ hoàn tiền</option>
                    <option value="refunded" @selected(request('status') === 'refunded')>Đã hoàn tiền</option>
                    <option value="non_refundable" @selected(request('status') === 'non_refundable')>Không hoàn tiền</option>
                </select>
            </div>

            <div class="col-md-2 d-flex align-items-end gap-2">
                <button class="btn btn-primary w-100">Lọc</button>
                <a href="{{ route('admin.refunds.index') }}" class="btn btn-outline-secondary">Xóa</a>
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
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>Khách sạn</th>
                    <th>Owner</th>
                    <th>Lý do hủy</th>
                    <th>Số tiền thanh toán</th>
                    <th>Số tiền hoàn</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
                </thead>

                <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payment->booking->booking_code }}</td>

                        <td>
                            <div>{{ $payment->booking->contact_name }}</div>
                            <div class="text-muted small">{{ $payment->booking->contact_phone }}</div>
                        </td>

                        <td>{{ $payment->booking->hotel->name }}</td>

                        <td>
                            <div>{{ optional($payment->booking->hotel->owner)->name ?: '-' }}</div>
                            <div class="text-muted small">{{ optional($payment->booking->hotel->owner)->email ?: '' }}</div>
                        </td>

                        <td class="small text-muted">
                            {{ \Illuminate\Support\Str::limit($payment->booking->cancel_reason ?: '-', 70) }}
                        </td>

                        <td>{{ number_format($payment->amount, 0, ',', '.') }}đ</td>

                        <td>{{ number_format($payment->refund_amount ?? 0, 0, ',', '.') }}đ</td>

                        <td>
                            @if($payment->status === 'refunding')
                                <span class="badge bg-warning text-dark">Chờ hoàn tiền</span>
                            @elseif($payment->status === 'refunded')
                                <span class="badge bg-success">Đã hoàn tiền</span>
                            @else
                                <span class="badge bg-danger">Không hoàn tiền</span>
                            @endif
                        </td>

                        <td class="text-end">
                            <a href="{{ route('admin.refunds.show', $payment) }}" class="btn btn-sm btn-outline-primary">
                                Xem
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            Chưa có yêu cầu hoàn tiền nào.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $payments->links() }}
        </div>
    </div>
</div>
@endsection
