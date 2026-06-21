@extends('layouts.dashboard')

@section('title', 'Doanh thu & đối soát')
@section('page-title', 'Doanh thu & đối soát')

@section('content')
<div class="tm-page-toolbar">
    <div>
        <div class="tm-eyebrow"><i class="bi bi-cash-coin"></i> Owner revenue</div>
        <h2 class="tm-heading-md mb-2">Doanh thu & đối soát</h2>
        <p class="tm-lead mb-0">
            Theo dõi doanh thu, phí nền tảng, khoản Owner nhận và các khoản điều chỉnh phát sinh từ hoàn tiền sau đối soát.
        </p>
    </div>

    <a href="{{ route('profile.payment-info') }}" class="btn tm-btn-primary px-4 py-3">
        <i class="bi bi-bank me-2"></i>Thông tin ngân hàng
    </a>
</div>

<div class="tm-kpi-grid mb-4">
    <div class="tm-kpi-card" data-aos="fade-up">
        <div class="d-flex justify-content-between align-items-start gap-3">
            <div>
                <span>Tổng doanh thu</span>
                <strong>{{ number_format($totalGross, 0, ',', '.') }}đ</strong>
                <small>Tổng tiền booking đã ghi nhận.</small>
            </div>
            <div class="tm-kpi-icon"><i class="bi bi-graph-up-arrow"></i></div>
        </div>
    </div>
    <div class="tm-kpi-card" data-aos="fade-up" data-aos-delay="60">
        <div class="d-flex justify-content-between align-items-start gap-3">
            <div>
                <span>Phí nền tảng</span>
                <strong>{{ number_format($totalFee, 0, ',', '.') }}đ</strong>
                <small>Khoản Travel Mate giữ lại.</small>
            </div>
            <div class="tm-kpi-icon"><i class="bi bi-percent"></i></div>
        </div>
    </div>
    <div class="tm-kpi-card" data-aos="fade-up" data-aos-delay="120">
        <div class="d-flex justify-content-between align-items-start gap-3">
            <div>
                <span>Owner nhận</span>
                <strong>{{ number_format($totalOwnerAmount, 0, ',', '.') }}đ</strong>
                <small>Tổng phần doanh thu thuộc Owner.</small>
            </div>
            <div class="tm-kpi-icon"><i class="bi bi-wallet2"></i></div>
        </div>
    </div>
    <div class="tm-kpi-card" data-aos="fade-up" data-aos-delay="180">
        <div class="d-flex justify-content-between align-items-start gap-3">
            <div>
                <span>Đã đối soát</span>
                <strong>{{ number_format($settledAmount, 0, ',', '.') }}đ</strong>
                <small>Các khoản đã được Admin xác nhận.</small>
            </div>
            <div class="tm-kpi-icon"><i class="bi bi-check2-circle"></i></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="tm-soft-panel h-100">
            <div class="tm-eyebrow mb-2"><i class="bi bi-hourglass-split"></i> Chờ đối soát</div>
            <h3 class="fw-bold mb-1">{{ number_format($waitingAmount, 0, ',', '.') }}đ</h3>
            <div class="tm-status-note">Khoản đủ điều kiện hoặc đang chờ Admin đối soát.</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="tm-policy-box h-100">
            <div class="fw-bold text-uppercase small mb-2"><i class="bi bi-exclamation-triangle me-1"></i>Công nợ chờ trừ</div>
            <h3 class="fw-bold mb-1">{{ number_format($pendingAdjustmentAmount, 0, ',', '.') }}đ</h3>
            <div class="small">
                Các khoản khấu trừ có thể đến từ hoàn tiền cho khách sau khi booking trước đó đã được đối soát.
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="tm-success-box h-100">
            <div class="fw-bold text-uppercase small mb-2"><i class="bi bi-cash-stack me-1"></i>Ước tính thực nhận kỳ tới</div>
            <h3 class="fw-bold mb-1">{{ number_format($estimatedNetWaitingAmount, 0, ',', '.') }}đ</h3>
            <div class="small">Đã trừ các khoản điều chỉnh đang chờ nếu có.</div>
        </div>
    </div>
</div>

<div class="tm-neutral-box mb-4">
    Owner chỉ theo dõi doanh thu, settlement và các khoản khấu trừ do refund sau settlement. Quyết định hoàn tiền hoặc không hoàn tiền do Admin Travel Mate xử lý.
</div>

<div class="tm-card p-4 mb-4" data-aos="fade-up">
    <form method="GET" action="{{ route('owner.revenues.index') }}" class="row g-3 align-items-end">
        <div class="col-md-8">
            <div class="tm-field">
                <label>Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả trạng thái</option>
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
        </div>

        <div class="col-md-4 d-flex align-items-end gap-2">
            <button class="btn tm-btn-primary w-100 py-3">
                <i class="bi bi-funnel me-1"></i>Lọc
            </button>
            <a href="{{ route('owner.revenues.index') }}" class="btn tm-btn-light py-3">
                Xóa
            </a>
        </div>
    </form>
</div>

<div class="tm-card p-4 mb-4" data-aos="fade-up" data-aos-delay="80">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
        <div>
            <div class="tm-eyebrow mb-1"><i class="bi bi-list-check"></i> Giao dịch doanh thu</div>
            <h5 class="fw-bold mb-0">Các booking đã ghi nhận doanh thu</h5>
        </div>
        <span class="tm-mini-badge">{{ $transactions->count() }} dòng trong trang</span>
    </div>

    <div class="table-responsive">
        <table class="table tm-table align-middle mb-0">
            <thead>
            <tr>
                <th>Mã đơn</th>
                <th>Khách sạn</th>
                <th>Tổng tiền</th>
                <th>Phí nền tảng</th>
                <th>Owner nhận</th>
                <th>Trạng thái</th>
                <th>Đối soát</th>
            </tr>
            </thead>

            <tbody>
            @forelse($transactions as $transaction)
                <tr>
                    <td class="fw-bold">{{ $transaction->booking->booking_code }}</td>

                    <td>
                        <div class="fw-bold">{{ $transaction->booking->hotel->name }}</div>
                        <div class="text-muted small">
                            {{ $transaction->booking->checkin_date->format('d/m/Y') }}
                            -
                            {{ $transaction->booking->checkout_date->format('d/m/Y') }}
                        </div>
                    </td>

                    <td>{{ number_format($transaction->gross_amount, 0, ',', '.') }}đ</td>
                    <td>{{ number_format($transaction->platform_fee, 0, ',', '.') }}đ</td>
                    <td class="fw-semibold text-primary">{{ number_format($transaction->owner_amount, 0, ',', '.') }}đ</td>

                    <td>
                        @include('admin.settlements._status', ['status' => $transaction->status])
                    </td>

                    <td>
                        @if($transaction->settlement)
                            <div>Thực chuyển: <strong>{{ number_format($transaction->settlement->amount, 0, ',', '.') }}đ</strong></div>
                            <div class="text-muted small">
                                Mã CK: {{ $transaction->settlement->transfer_code }}<br>
                                {{ $transaction->settlement->settled_at?->format('d/m/Y H:i') }}
                            </div>
                        @else
                            <span class="text-muted">Chưa đối soát</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <div class="tm-empty-state">
                            <i class="bi bi-cash-coin"></i>
                            Chưa có doanh thu nào.
                        </div>
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

<div class="tm-card p-4" data-aos="fade-up" data-aos-delay="120">
    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
        <div>
            <div class="tm-eyebrow mb-1"><i class="bi bi-arrow-left-right"></i> Điều chỉnh công nợ</div>
            <h5 class="fw-bold mb-0">Các khoản trừ do refund sau đối soát</h5>
        </div>
        <span class="badge bg-warning text-dark">Chờ trừ: {{ number_format($pendingAdjustmentAmount, 0, ',', '.') }}đ</span>
    </div>

    <div class="tm-neutral-box mb-3">
        Nếu khách được hoàn tiền sau khi đơn đã được quyết toán cho Owner, hệ thống ghi nhận khoản điều chỉnh tại đây và trừ vào kỳ đối soát tiếp theo. Khoản đã trừ lũy kế: <strong>{{ number_format($deductedAdjustmentAmount, 0, ',', '.') }}đ</strong>.
    </div>

    <div class="table-responsive">
        <table class="table tm-table align-middle mb-0">
            <thead>
            <tr>
                <th>Mã đơn</th>
                <th>Loại</th>
                <th>Lý do</th>
                <th class="text-end">Số tiền</th>
                <th class="text-end">Còn phải trừ</th>
                <th>Trạng thái</th>
            </tr>
            </thead>

            <tbody>
            @forelse($adjustments as $adjustment)
                <tr>
                    <td class="fw-bold">{{ $adjustment->booking->booking_code ?? '-' }}</td>
                    <td>
                        @if($adjustment->type === 'refund_clawback')
                            Hoàn tiền sau đối soát
                        @else
                            {{ $adjustment->type }}
                        @endif
                    </td>
                    <td class="small text-muted">{{ $adjustment->reason ?: '-' }}</td>
                    <td class="text-end text-danger fw-semibold">{{ number_format($adjustment->amount, 0, ',', '.') }}đ</td>
                    <td class="text-end">{{ number_format($adjustment->remaining_amount, 0, ',', '.') }}đ</td>
                    <td>
                        @if($adjustment->status === 'pending_deduction')
                            <span class="badge bg-warning text-dark">Chờ trừ</span>
                        @elseif($adjustment->status === 'deducted')
                            <span class="badge bg-success">Đã trừ</span>
                        @else
                            <span class="badge bg-secondary">{{ $adjustment->status }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <div class="tm-empty-state">
                            <i class="bi bi-check2-circle"></i>
                            Chưa có khoản điều chỉnh công nợ nào.
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $adjustments->links() }}
    </div>
</div>
@endsection
