@extends('layouts.dashboard')

@section('title', 'Quản lý đơn đặt phòng')
@section('page-title', 'Quản lý đơn đặt phòng')

@section('content')
<div class="tm-page-toolbar">
    <div>
        <div class="tm-eyebrow"><i class="bi bi-calendar-check"></i> Vận hành lưu trú</div>
        <h4 class="fw-black mb-1">Danh sách đơn đặt phòng</h4>
        <p class="text-muted fw-semibold mb-0">Theo dõi thanh toán, check-in, check-out và các đơn cần xử lý thủ công.</p>
    </div>
    <span class="tm-dashboard-chip"><i class="bi bi-list-task"></i> {{ $bookings->total() }} đơn</span>
</div>

<div class="tm-form-card mb-4">
    <form method="GET" action="{{ route('owner.bookings.index') }}" class="row g-3 align-items-end">
        <div class="col-lg-6">
            <div class="tm-field">
                <label><i class="bi bi-search me-1"></i> Từ khóa</label>
                <input
                    type="text"
                    name="keyword"
                    class="form-control"
                    value="{{ request('keyword') }}"
                    placeholder="Mã đơn, tên khách, số điện thoại"
                >
            </div>
        </div>

        <div class="col-lg-4">
            <div class="tm-field">
                <label>Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả trạng thái</option>
                    @foreach([
                        'pending_payment' => 'Chờ thanh toán',
                        'confirmed' => 'Đã xác nhận',
                        'staying' => 'Đang lưu trú',
                        'completed' => 'Hoàn tất',
                        'cancelled' => 'Đã hủy',
                        'no_show' => 'No-show',
                        'manual_review' => 'Cần xử lý',
                    ] as $key => $label)
                        <option value="{{ $key }}" @selected(request('status') === $key)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-lg-2 d-flex gap-2">
            <button class="btn tm-btn-primary flex-fill" type="submit">
                <i class="bi bi-funnel me-1"></i> Lọc
            </button>
        </div>
    </form>
</div>

<div class="tm-surface p-3 p-lg-4">
    <div class="table-responsive">
        <table class="table tm-table align-middle">
            <thead>
            <tr>
                <th>Mã đơn</th>
                <th>Khách hàng</th>
                <th>Khách sạn</th>
                <th>Thời gian</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th class="text-end">Thao tác</th>
            </tr>
            </thead>

            <tbody>
            @forelse($bookings as $booking)
                <tr>
                    <td>
                        <strong>{{ $booking->booking_code }}</strong>
                        @if($booking->payment)
                            <div class="text-muted small">{{ strtoupper($booking->payment->method) }} · {{ $booking->payment->status }}</div>
                        @endif
                    </td>

                    <td>
                        <div class="fw-black">{{ $booking->contact_name }}</div>
                        <div class="text-muted small"><i class="bi bi-telephone me-1"></i>{{ $booking->contact_phone }}</div>
                    </td>

                    <td>
                        <div class="fw-semibold">{{ $booking->hotel->name }}</div>
                        <div class="text-muted small">{{ $booking->guest_count }} khách</div>
                    </td>

                    <td>
                        <div class="fw-semibold">{{ $booking->checkin_date->format('d/m/Y') }}</div>
                        <div class="text-muted small">đến {{ $booking->checkout_date->format('d/m/Y') }}</div>
                    </td>

                    <td><strong class="text-primary">{{ number_format($booking->total_amount, 0, ',', '.') }}đ</strong></td>

                    <td>
                        @include('owner.bookings._status', ['status' => $booking->status])
                    </td>

                    <td class="text-end">
                        <div class="tm-action-stack">
                            <a href="{{ route('owner.bookings.show', $booking) }}" class="btn btn-sm tm-btn-light">
                                <i class="bi bi-eye me-1"></i> Xem
                            </a>

                            @if($booking->status === 'confirmed')
                                <a href="{{ route('owner.bookings.check-in.create', $booking) }}" class="btn btn-sm btn-success">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> Check-in
                                </a>
                            @endif

                            @if($booking->status === 'staying')
                                <form
                                    method="POST"
                                    action="{{ route('owner.bookings.check-out.store', $booking) }}"
                                    class="d-inline"
                                    onsubmit="return confirm('Xác nhận check-out đơn này?')"
                                >
                                    @csrf
                                    <button class="btn btn-sm btn-info text-white" type="submit">
                                        <i class="bi bi-box-arrow-right me-1"></i> Check-out
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
                            <i class="bi bi-calendar-x"></i>
                            Chưa có đơn đặt phòng phù hợp.
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $bookings->links() }}
    </div>
</div>
@endsection
