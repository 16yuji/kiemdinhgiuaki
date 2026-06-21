@extends('layouts.dashboard')

@section('title', 'Chi tiết hoàn tiền')
@section('page-title', 'Chi tiết hoàn tiền')

@section('content')
@php
    $booking = $payment->booking;
    $hotel = $booking->hotel;
    $financialTransaction = $booking->financialTransaction ?? null;
    $isSettled = $financialTransaction && $financialTransaction->status === 'settled';
@endphp

<div class="tm-page-toolbar">
    <div>
        <div class="tm-eyebrow"><i class="bi bi-arrow-counterclockwise"></i> Refund review</div>
        <h2 class="tm-heading-md mb-2">Hoàn tiền đơn {{ $booking->booking_code }}</h2>
        <div class="tm-status-stack">
            @if($payment->status === 'refunding')
                <span class="badge bg-warning text-dark">Chờ Admin kiểm tra hoàn tiền</span>
            @elseif($payment->status === 'refunded')
                <span class="badge bg-success">Đã hoàn tiền</span>
            @elseif($payment->status === 'non_refundable')
                <span class="badge bg-danger">Không hoàn tiền</span>
            @else
                <span class="badge bg-secondary">{{ $payment->status }}</span>
            @endif
            <span class="tm-mini-badge"><i class="bi bi-calendar-event"></i> Hủy lúc {{ $booking->cancelled_at ? $booking->cancelled_at->format('d/m/Y H:i') : '-' }}</span>
        </div>
    </div>

    <a href="{{ route('admin.refunds.index') }}" class="btn tm-btn-light px-4">
        <i class="bi bi-arrow-left me-2"></i>Quay lại
    </a>
</div>

@if($isSettled)
    <div class="tm-danger-box mb-4" data-aos="fade-up">
        <strong><i class="bi bi-exclamation-triangle me-1"></i>This booking has already been settled to Owner.</strong>
        Confirming refund will create an Owner adjustment/clawback and deduct it from the next settlement.
        <div class="small mt-2">Khoản clawback được tính theo phần doanh thu Owner đã nhận tương ứng với số tiền hoàn cho khách.</div>
    </div>
@endif

<div class="row g-4">
    <div class="col-xl-7">
        <div class="tm-card p-4 mb-4" data-aos="fade-up">
            <div class="tm-eyebrow"><i class="bi bi-receipt"></i> Booking & payment</div>
            <div class="tm-meta-card">
                <div>
                    <span>Mã đơn</span>
                    <strong>{{ $booking->booking_code }}</strong>
                </div>
                <div>
                    <span>Thời gian lưu trú</span>
                    <strong>{{ $booking->checkin_date->format('d/m/Y') }} - {{ $booking->checkout_date->format('d/m/Y') }}</strong>
                </div>
                <div>
                    <span>Số tiền thanh toán</span>
                    <strong>{{ number_format($payment->amount, 0, ',', '.') }}đ</strong>
                </div>
                <div>
                    <span>Mã giao dịch</span>
                    <strong>{{ $payment->transaction_code ?: '-' }}</strong>
                </div>
                <div>
                    <span>Ngày thanh toán</span>
                    <strong>{{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : '-' }}</strong>
                </div>
                <div>
                    <span>Đối soát nội bộ</span>
                    <strong>
                        @if($financialTransaction)
                            {{ $financialTransaction->status }}
                        @else
                            Chưa có ghi nhận
                        @endif
                    </strong>
                </div>
            </div>
        </div>

        <div class="tm-card p-4 mb-4" data-aos="fade-up" data-aos-delay="60">
            <div class="tm-eyebrow"><i class="bi bi-people"></i> Các bên liên quan</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="tm-soft-panel h-100">
                        <h6 class="fw-bold mb-3"><i class="bi bi-person me-1"></i>Khách hàng</h6>
                        <div class="tm-info-list">
                            <div><span>Tên liên hệ</span><strong>{{ $booking->contact_name }}</strong></div>
                            <div><span>Số điện thoại</span><strong>{{ $booking->contact_phone }}</strong></div>
                            <div><span>Email</span><strong>{{ $booking->contact_email ?: '-' }}</strong></div>
                            <div><span>Tài khoản</span><strong>{{ $booking->customer->name ?? '-' }}</strong></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="tm-soft-panel h-100">
                        <h6 class="fw-bold mb-3"><i class="bi bi-building me-1"></i>Khách sạn</h6>
                        <div class="tm-info-list">
                            <div><span>Tên khách sạn</span><strong>{{ $hotel->name }}</strong></div>
                            <div><span>Địa chỉ</span><strong>{{ $hotel->address ?? '-' }} @if($hotel->district), {{ $hotel->district }}@endif @if($hotel->province), {{ $hotel->province }}@endif</strong></div>
                            <div><span>Owner</span><strong>{{ $hotel->owner->name ?? '-' }}</strong></div>
                            <div><span>Email Owner</span><strong>{{ optional($hotel->owner)->email ?: '-' }}</strong></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tm-card p-4" data-aos="fade-up" data-aos-delay="120">
            <div class="tm-eyebrow"><i class="bi bi-shield-check"></i> Chính sách & lý do hủy</div>
            <div class="tm-policy-box mb-3">
                <strong>Chính sách hủy / hoàn tiền của khách sạn:</strong>
                <div class="mt-2">
                    {{ $hotel->cancellation_policy ?: 'Khách sạn chưa cập nhật chính sách hủy / hoàn tiền. Admin cần liên hệ Owner để xác minh trước khi xử lý.' }}
                </div>
            </div>
            <div class="tm-neutral-box">
                <strong>Lý do khách gửi:</strong>
                <div class="mt-2 text-muted">{{ $booking->cancel_reason ?: '-' }}</div>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="tm-payment-aside">
            <div class="tm-transfer-card mb-4" data-aos="fade-left">
                <span>Yêu cầu hoàn hiện tại</span>
                <strong>{{ number_format($payment->refund_amount ?: $payment->amount, 0, ',', '.') }}đ</strong>
                <div class="text-white-50 fw-bold mt-2">Admin đối chiếu chính sách trước khi xác nhận.</div>
            </div>

            @if($payment->status === 'refunding')
                <div class="tm-form-card" data-aos="fade-left" data-aos-delay="80">
                    <div class="tm-eyebrow"><i class="bi bi-clipboard-check"></i> Form xử lý</div>
                    <div class="tm-soft-panel mb-3">
                        <strong>Checklist xử lý</strong>
                        <ul class="small text-muted mb-0 mt-2 ps-3">
                            <li>Kiểm tra thời điểm khách hủy so với ngày nhận phòng.</li>
                            <li>Đối chiếu chính sách hủy / hoàn tiền của khách sạn.</li>
                            <li>Kiểm tra trạng thái thanh toán và đối soát nội bộ.</li>
                        </ul>
                    </div>

                    @if($isSettled)
                        <div class="tm-danger-box mb-3">
                            <strong>Cần tạo owner adjustment/clawback.</strong>
                            Khoản này đã settlement, hoàn tiền cho khách sẽ ảnh hưởng kỳ đối soát sau của Owner.
                        </div>
                    @endif

                    <div class="tm-decision-grid">
                        <form method="POST" action="{{ route('admin.refunds.refunded', $payment) }}" class="js-confirm-form" data-confirm="Xác nhận đã hoàn tiền cho khách?">
                            @csrf

                            <div class="tm-field mb-3">
                                <label>Số tiền hoàn <span class="text-danger">*</span></label>
                                <input
                                    type="number"
                                    name="refund_amount"
                                    class="form-control @error('refund_amount') is-invalid @enderror"
                                    min="0"
                                    max="{{ $payment->amount }}"
                                    value="{{ old('refund_amount', $payment->refund_amount ?: $payment->amount) }}"
                                    required
                                >
                                @error('refund_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="tm-field mb-3">
                                <label>Ghi chú hoàn tiền cho khách</label>
                                <textarea
                                    name="refund_note"
                                    rows="4"
                                    class="form-control"
                                    placeholder="Ví dụ: Hoàn 100% vì khách hủy trước hạn theo chính sách khách sạn."
                                >{{ old('refund_note') }}</textarea>
                            </div>

                            <button class="btn btn-success w-100 py-3">
                                <i class="bi bi-check2-circle me-2"></i>Xác nhận đã hoàn tiền
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.refunds.non-refundable', $payment) }}" class="js-confirm-form" data-confirm="Xác nhận không hoàn tiền đơn này?">
                            @csrf

                            <div class="tm-field mb-3">
                                <label>Lý do không hoàn tiền <span class="text-danger">*</span></label>
                                <textarea
                                    name="refund_note"
                                    rows="4"
                                    class="form-control @error('refund_note') is-invalid @enderror"
                                    placeholder="Ví dụ: Khách hủy sau hạn cho phép theo chính sách hủy của khách sạn."
                                    required
                                >{{ old('refund_note') }}</textarea>
                                @error('refund_note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button class="btn btn-outline-danger w-100 py-3">
                                <i class="bi bi-x-circle me-2"></i>Xác nhận không hoàn tiền
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="tm-form-card" data-aos="fade-left" data-aos-delay="80">
                    <div class="tm-eyebrow"><i class="bi bi-clipboard-data"></i> Kết quả xử lý</div>

                    @if($payment->status === 'refunded')
                        <div class="tm-success-box mb-3">Đơn đã được hoàn tiền cho khách.</div>
                    @elseif($payment->status === 'non_refundable')
                        <div class="tm-danger-box mb-3">Đơn được xác nhận không hoàn tiền.</div>
                    @endif

                    <div class="tm-info-list">
                        <div><span>Số tiền hoàn</span><strong>{{ number_format($payment->refund_amount ?? 0, 0, ',', '.') }}đ</strong></div>
                        <div><span>Ghi chú</span><strong>{{ $payment->refund_note ?: '-' }}</strong></div>
                        <div><span>Thời gian xử lý</span><strong>{{ $payment->refunded_at ? $payment->refunded_at->format('d/m/Y H:i') : '-' }}</strong></div>
                        <div><span>Người xử lý</span><strong>{{ $payment->refundedBy->name ?? '-' }}</strong></div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
