@extends('layouts.dashboard')

@section('title', 'Chi tiết đơn đặt phòng')
@section('page-title', 'Chi tiết đơn đặt phòng')

@section('content')
@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="tm-page-toolbar">
    <div>
        <div class="tm-eyebrow"><i class="bi bi-calendar-check"></i> Booking operation</div>
        <h2 class="tm-heading-md mb-2">Đơn {{ $booking->booking_code }}</h2>
        <div class="tm-status-stack">
            @include('owner.bookings._status', ['status' => $booking->status])
            <span class="tm-mini-badge"><i class="bi bi-building"></i>{{ $booking->hotel->name }}</span>
            <span class="tm-mini-badge"><i class="bi bi-people"></i>{{ $booking->guest_count }} khách</span>
        </div>
    </div>

    <a href="{{ route('owner.bookings.index') }}" class="btn tm-btn-light px-4">
        <i class="bi bi-arrow-left me-2"></i>Quay lại
    </a>
</div>

@if($booking->manual_review_reason)
    <div class="tm-policy-box mb-4">
        <strong>Lý do cần xử lý thủ công:</strong>
        {{ $booking->manual_review_reason }}
    </div>
@endif

@if($booking->status === 'manual_review')
    <div class="tm-form-card mb-4">
        <div class="tm-eyebrow"><i class="bi bi-tools"></i> Xử lý thủ công</div>
        <p class="text-muted fw-semibold">
            Đơn bị đưa vào trạng thái cần xử lý vì hệ thống không tìm thấy đủ phòng sẵn sàng khi check-in.
            Hãy kiểm tra danh sách phòng, chuyển phòng phù hợp sang trạng thái sẵn sàng rồi mở lại đơn.
        </p>

        <form
            method="POST"
            action="{{ route('owner.bookings.resolve-manual-review', $booking) }}"
            onsubmit="return confirm('Xác nhận đã xử lý thủ công và mở lại đơn để check-in?')"
        >
            @csrf
            <div class="tm-field mb-3">
                <label>Ghi chú xử lý</label>
                <textarea
                    name="manual_review_note"
                    rows="3"
                    class="form-control"
                    placeholder="Ví dụ: Đã chuyển phòng R46-201 sang Sẵn sàng để tiếp nhận khách."
                >{{ old('manual_review_note') }}</textarea>
            </div>

            <button type="submit" class="btn btn-warning">
                <i class="bi bi-check2-circle me-1"></i> Đã sắp xếp phòng, mở lại Check-in
            </button>
        </form>
    </div>
@endif

<div class="row g-4">
    <div class="col-xl-7">
        <div class="tm-card p-4 mb-4">
            <div class="tm-eyebrow"><i class="bi bi-receipt"></i> Thông tin đặt phòng</div>
            <div class="tm-meta-card">
                <div><span>Khách sạn</span><strong>{{ $booking->hotel->name }}</strong></div>
                <div><span>Khách hàng</span><strong>{{ $booking->customer->name ?? '-' }}</strong></div>
                <div><span>Ngày nhận phòng</span><strong>{{ $booking->checkin_date->format('d/m/Y') }}</strong></div>
                <div><span>Ngày trả phòng</span><strong>{{ $booking->checkout_date->format('d/m/Y') }}</strong></div>
                <div><span>Người liên hệ</span><strong>{{ $booking->contact_name }}</strong></div>
                <div><span>SĐT</span><strong>{{ $booking->contact_phone }}</strong></div>
                <div><span>Email</span><strong>{{ $booking->contact_email ?: '-' }}</strong></div>
                <div><span>Tổng tiền</span><strong class="text-primary">{{ number_format($booking->total_amount, 0, ',', '.') }}đ</strong></div>
            </div>

            @if($booking->special_request)
                <div class="tm-neutral-box mt-3">
                    <strong>Yêu cầu đặc biệt:</strong>
                    <p class="mb-0 mt-1">{{ $booking->special_request }}</p>
                </div>
            @endif
        </div>

        <div class="tm-card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="tm-eyebrow mb-0"><i class="bi bi-door-open"></i> Hạng phòng đã đặt</div>
                <span class="tm-dashboard-chip">{{ $booking->roomTypes->sum('quantity') }} phòng</span>
            </div>

            <div class="table-responsive">
                <table class="table tm-table align-middle mb-0">
                    <thead>
                    <tr>
                        <th>Hạng phòng</th>
                        <th>Số lượng</th>
                        <th>Giá/đêm</th>
                        <th class="text-end">Tạm tính</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($booking->roomTypes as $item)
                        <tr>
                            <td class="fw-black">{{ $item->roomType->name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format($item->price_per_night, 0, ',', '.') }}đ</td>
                            <td class="text-end fw-black">{{ number_format($item->subtotal, 0, ',', '.') }}đ</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tm-card p-4">
            <div class="tm-eyebrow"><i class="bi bi-key"></i> Phòng đã gán</div>
            <div class="row g-3">
                @forelse($booking->roomAssignments as $assignment)
                    <div class="col-md-6">
                        <div class="tm-neutral-box h-100">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <strong>Phòng {{ $assignment->room->room_number ?? '-' }}</strong>
                                @if($assignment->room && $assignment->room->floor)
                                    <span class="tm-status tm-status-muted">Tầng {{ $assignment->room->floor }}</span>
                                @endif
                            </div>
                            <div class="text-muted small fw-semibold">Hạng phòng: {{ $assignment->room->roomType->name ?? '-' }}</div>
                            <div class="text-muted small fw-semibold">Gán lúc: {{ $assignment->assigned_at ? $assignment->assigned_at->format('d/m/Y H:i') : '-' }}</div>
                            <div class="text-muted small fw-semibold">Trả lúc: {{ $assignment->released_at ? $assignment->released_at->format('d/m/Y H:i') : '-' }}</div>
                            @if($assignment->note)
                                <div class="text-muted small fw-semibold">Ghi chú: {{ $assignment->note }}</div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="tm-empty-state">
                            <i class="bi bi-key"></i>
                            Chưa gán phòng cụ thể.
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="tm-payment-aside">
            <div class="tm-form-card mb-4">
                <div class="tm-eyebrow"><i class="bi bi-credit-card"></i> Thanh toán</div>
                @if($booking->payment)
                    <div class="tm-info-list">
                        <div><span>Phương thức</span><strong>{{ strtoupper($booking->payment->method) }}</strong></div>
                        <div><span>Trạng thái</span><strong>{{ $booking->payment->status }}</strong></div>
                        <div><span>Mã giao dịch</span><strong>{{ $booking->payment->transaction_code ?: '-' }}</strong></div>
                        <div><span>Ngày thanh toán</span><strong>{{ $booking->payment->paid_at ? $booking->payment->paid_at->format('d/m/Y H:i') : '-' }}</strong></div>
                    </div>
                @else
                    <div class="tm-empty-state py-3">
                        <i class="bi bi-credit-card"></i>
                        Chưa có thanh toán.
                    </div>
                @endif
            </div>

            @if($booking->status === 'confirmed')
                <div class="tm-form-card mb-4">
                    <div class="tm-eyebrow"><i class="bi bi-person-check"></i> Thao tác lưu trú</div>
                    <a href="{{ route('owner.bookings.check-in.create', $booking) }}" class="btn btn-success w-100 mb-3">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Check-in
                    </a>

                    <form
                        method="POST"
                        action="{{ route('owner.bookings.no-show.store', $booking) }}"
                        onsubmit="return confirm('Xác nhận khách không đến nhận phòng?')"
                    >
                        @csrf

                        <div class="tm-field mb-3">
                            <label>Lý do No-show</label>
                            <textarea
                                name="no_show_reason"
                                rows="3"
                                class="form-control"
                                placeholder="Ví dụ: Khách không đến, không liên hệ được..."
                            >{{ old('no_show_reason') }}</textarea>
                        </div>

                        <button class="btn btn-outline-danger w-100" type="submit">
                            <i class="bi bi-person-x me-1"></i> Ghi nhận No-show
                        </button>
                    </form>
                </div>
            @endif

            @if($booking->status === 'staying')
                <div class="tm-form-card mb-4">
                    <div class="tm-eyebrow"><i class="bi bi-arrow-left-right"></i> Đổi phòng</div>
                    <p class="text-muted fw-semibold small">
                        Phòng mới phải cùng hạng và đang ở trạng thái sẵn sàng.
                    </p>
                    <a href="{{ route('owner.bookings.change-room.create', $booking) }}" class="btn btn-warning w-100">
                        <i class="bi bi-arrow-left-right me-1"></i> Đổi phòng
                    </a>
                </div>

                <div class="tm-form-card mb-4">
                    <div class="tm-eyebrow"><i class="bi bi-box-arrow-right"></i> Check-out</div>
                    <form
                        method="POST"
                        action="{{ route('owner.bookings.check-out.store', $booking) }}"
                        onsubmit="return confirm('Xác nhận check-out đơn này?')"
                    >
                        @csrf

                        @if($booking->checkout_date->isFuture())
                            <div class="tm-policy-box mb-3">
                                Đơn đang được check-out sớm hơn ngày trả phòng dự kiến. Vui lòng nhập ghi chú.
                            </div>
                        @endif

                        <div class="tm-field mb-3">
                            <label>Ghi chú check-out</label>
                            <textarea
                                name="checkout_note"
                                rows="3"
                                class="form-control @error('checkout_note') is-invalid @enderror"
                                placeholder="Ví dụ: Khách trả phòng sớm, phòng không hư hỏng..."
                            >{{ old('checkout_note') }}</textarea>

                            @error('checkout_note')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <button class="btn btn-success w-100" type="submit">
                            <i class="bi bi-check2-circle me-1"></i> Xác nhận check-out
                        </button>
                    </form>
                </div>
            @endif

            @if($booking->financialTransaction)
                <div class="tm-form-card mb-4">
                    <div class="tm-eyebrow"><i class="bi bi-cash-coin"></i> Ghi nhận tài chính nội bộ</div>
                    <div class="tm-finance-breakdown">
                        <div class="tm-finance-row"><span>Tổng tiền</span><strong>{{ number_format($booking->financialTransaction->gross_amount, 0, ',', '.') }}đ</strong></div>
                        <div class="tm-finance-row"><span>Phí nền tảng</span><strong>{{ number_format($booking->financialTransaction->platform_fee, 0, ',', '.') }}đ</strong></div>
                        <div class="tm-finance-row"><span>Owner nhận</span><strong>{{ number_format($booking->financialTransaction->owner_amount, 0, ',', '.') }}đ</strong></div>
                        <div class="tm-finance-row"><span>Trạng thái</span><strong>{{ $booking->financialTransaction->status }}</strong></div>
                    </div>
                </div>
            @endif

            @if($booking->checkin_note || $booking->checkout_note || $booking->status === 'cancelled' || $booking->status === 'no_show')
                <div class="tm-form-card mb-4">
                    <div class="tm-eyebrow"><i class="bi bi-journal-text"></i> Ghi chú vận hành</div>
                    <div class="tm-info-list">
                        @if($booking->checkin_note)
                            <div><span>Check-in</span><strong>{{ $booking->checkin_note }}</strong></div>
                        @endif
                        @if($booking->checkout_note)
                            <div><span>Check-out</span><strong>{{ $booking->checkout_note }}</strong></div>
                        @endif
                        @if($booking->status === 'cancelled')
                            <div><span>Lý do hủy</span><strong>{{ $booking->cancel_reason ?: '-' }}</strong></div>
                            <div><span>Thời gian hủy</span><strong>{{ $booking->cancelled_at ? $booking->cancelled_at->format('d/m/Y H:i') : '-' }}</strong></div>
                        @endif
                        @if($booking->status === 'no_show')
                            <div><span>Lý do No-show</span><strong>{{ $booking->no_show_reason ?: '-' }}</strong></div>
                            <div><span>Thời gian ghi nhận</span><strong>{{ $booking->no_show_at ? $booking->no_show_at->format('d/m/Y H:i') : '-' }}</strong></div>
                        @endif
                    </div>
                </div>
            @endif

            @if($booking->review)
                <div class="tm-review-card">
                    <div class="tm-eyebrow"><i class="bi bi-chat-heart"></i> Đánh giá của khách</div>
                    <div class="text-warning fw-black mb-2">
                        <i class="bi bi-star-fill"></i> {{ $booking->review->rating }}/5
                    </div>
                    <p class="mb-0">{{ $booking->review->comment ?: 'Không có nhận xét.' }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
