@extends('layouts.frontend')

@section('title', 'Chi tiết đơn đặt phòng')

@section('content')
@php
    $paymentStatus = optional($booking->payment)->status;
    $isPaidBooking = $paymentStatus === 'paid';
    $isUnpaidBooking = in_array($paymentStatus, ['pending', 'failed', 'expired'], true) || !$booking->payment;

    $canCancelOnline = in_array($booking->status, ['pending_payment', 'confirmed'], true)
        && $booking->checkin_date->isFuture();

    $isCancelBlockedByDate = in_array($booking->status, ['pending_payment', 'confirmed'], true)
        && ($booking->checkin_date->isToday() || $booking->checkin_date->isPast());

    $canContinuePayment = $booking->status === 'pending_payment';
    $canReview = $booking->status === 'completed' && !$booking->review;

    $adminPhone = '1900 9999';
    $adminEmail = 'support@travelmate.local';
@endphp

<section class="tm-section pb-4">
    <div class="container">
        <div class="tm-page-hero" data-aos="fade-up">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="tm-eyebrow"><i class="bi bi-receipt-cutoff"></i> Chi tiết đặt phòng</div>
                    <h1 class="tm-heading-md mb-2">Đơn {{ $booking->booking_code }}</h1>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        @include('customer.bookings._status', ['status' => $booking->status])
                        @if($booking->payment)
                            <span class="tm-mini-badge"><i class="bi bi-credit-card"></i> {{ strtoupper($booking->payment->method ?? 'PAYMENT') }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="{{ route('customer.bookings.history') }}" class="btn tm-btn-light px-4 py-3">
                        <i class="bi bi-arrow-left me-2"></i>Lịch sử đặt phòng
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="tm-section pt-0">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="tm-card p-4 mb-4" data-aos="fade-up">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <div class="tm-eyebrow"><i class="bi bi-buildings"></i> Thông tin đặt phòng</div>
                            <h2 class="h4 fw-black mb-0">{{ $booking->hotel->name }}</h2>
                            <p class="text-muted small mb-0 mt-1">
                                {{ $booking->hotel->address ?? '' }}
                                @if($booking->hotel->district), {{ $booking->hotel->district }}@endif
                                @if($booking->hotel->province), {{ $booking->hotel->province }}@endif
                            </p>
                        </div>
                    </div>

                    <div class="tm-info-grid">
                        <div class="tm-info-item"><span>Ngày nhận phòng</span><strong>{{ $booking->checkin_date->format('d/m/Y') }}</strong></div>
                        <div class="tm-info-item"><span>Ngày trả phòng</span><strong>{{ $booking->checkout_date->format('d/m/Y') }}</strong></div>
                        <div class="tm-info-item"><span>Số khách</span><strong>{{ $booking->guest_count ?? $booking->guests }} khách</strong></div>
                        <div class="tm-info-item"><span>Người liên hệ</span><strong>{{ $booking->contact_name }}</strong></div>
                        <div class="tm-info-item"><span>Số điện thoại</span><strong>{{ $booking->contact_phone }}</strong></div>
                        <div class="tm-info-item"><span>Email</span><strong>{{ $booking->contact_email ?: '-' }}</strong></div>
                    </div>

                    @if($booking->special_request)
                        <div class="rounded-4 p-3 mt-3" style="background:rgba(6,13,170,.055);">
                            <strong>Yêu cầu đặc biệt:</strong>
                            <span class="text-muted">{{ $booking->special_request }}</span>
                        </div>
                    @endif

                    @if($booking->status === 'cancelled')
                        <div class="alert alert-warning mt-3 mb-0">
                            <strong>Lý do hủy:</strong> {{ $booking->cancel_reason ?: 'Không có lý do cụ thể.' }}
                            @if($booking->cancelled_at)
                                <br><strong>Thời gian hủy:</strong> {{ $booking->cancelled_at->format('d/m/Y H:i') }}
                            @endif
                        </div>
                    @endif

                    @if($booking->status === 'no_show')
                        <div class="alert alert-dark mt-3 mb-0">
                            <strong>Trạng thái:</strong> Khách không đến nhận phòng.
                            @if($booking->no_show_reason)
                                <br><strong>Lý do:</strong> {{ $booking->no_show_reason }}
                            @endif
                            @if($booking->no_show_at)
                                <br><strong>Thời gian ghi nhận:</strong> {{ $booking->no_show_at->format('d/m/Y H:i') }}
                            @endif
                        </div>
                    @endif
                </div>

                @include('shared.hotel-map', [
                    'hotel' => $booking->hotel,
                    'mapId' => 'booking-hotel-map',
                    'heading' => 'Ban do khach san',
                    'class' => 'mb-4',
                ])

                <div class="tm-card p-4 mb-4" data-aos="fade-up" data-aos-delay="80">
                    <div class="tm-eyebrow"><i class="bi bi-door-open"></i> Hạng phòng đã đặt</div>
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
                                    <td>
                                        <div class="fw-bold">{{ $item->roomType->name }}</div>
                                        <div class="small text-muted">{{ $item->roomType->bed_type ?? 'Hạng phòng đã chọn' }}</div>
                                    </td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ number_format($item->price_per_night, 0, ',', '.') }}đ</td>
                                    <td class="text-end fw-bold">{{ number_format($item->subtotal, 0, ',', '.') }}đ</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        <div class="tm-total-pill">
                            Tổng tiền khách thanh toán: {{ number_format($booking->total_amount, 0, ',', '.') }}đ
                        </div>
                    </div>
                </div>

                @if($booking->status === 'completed')
                    <div class="tm-card p-4" data-aos="fade-up" data-aos-delay="120">
                        <div class="tm-eyebrow"><i class="bi bi-star"></i> Đánh giá dịch vụ</div>
                        @if($booking->review)
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="text-warning fs-5">★</span>
                                <strong>{{ $booking->review->rating }}/5</strong>
                            </div>
                            <p class="text-muted mb-0">{{ $booking->review->comment ?: 'Không có nhận xét.' }}</p>
                        @else
                            <p class="text-muted">Đơn đã hoàn tất. Bạn có thể đánh giá trải nghiệm lưu trú.</p>
                            <a href="{{ route('customer.reviews.create', $booking) }}" class="btn tm-btn-primary px-4">
                                <i class="bi bi-pencil-square me-2"></i>Viết đánh giá
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            <div class="col-lg-5">
                <div class="tm-sticky-panel">
                    <div class="tm-card p-4 mb-4" data-aos="fade-left">
                        <div class="tm-eyebrow"><i class="bi bi-credit-card"></i> Thanh toán</div>
                        @if($booking->payment)
                            <div class="tm-info-list">
                                <div><span>Phương thức</span><strong>{{ strtoupper($booking->payment->method ?? 'PAYMENT') }}</strong></div>
                                <div>
                                    <span>Trạng thái</span>
                                    <strong>
                                        @switch($booking->payment->status)
                                            @case('pending') <span class="badge bg-warning text-dark">Chờ thanh toán</span> @break
                                            @case('paid') <span class="badge bg-success">Đã thanh toán</span> @break
                                            @case('failed') <span class="badge bg-danger">Thanh toán thất bại</span> @break
                                            @case('expired') <span class="badge bg-secondary">Hết hạn thanh toán</span> @break
                                            @case('refunding') <span class="badge bg-warning text-dark">Chờ Admin kiểm tra hoàn tiền</span> @break
                                            @case('refunded') <span class="badge bg-success">Đã hoàn tiền</span> @break
                                            @case('non_refundable') <span class="badge bg-danger">Không hoàn tiền</span> @break
                                            @default <span class="badge bg-secondary">{{ $booking->payment->status }}</span>
                                        @endswitch
                                    </strong>
                                </div>
                                <div><span>Ngày thanh toán</span><strong>{{ $booking->payment->paid_at ? $booking->payment->paid_at->format('d/m/Y H:i') : '-' }}</strong></div>
                                <div><span>Số tiền</span><strong>{{ number_format($booking->payment->amount ?? $booking->total_amount, 0, ',', '.') }}đ</strong></div>
                            </div>

                            @if(in_array($booking->payment->status, ['refunding', 'refunded', 'non_refundable'], true))
                                <div class="rounded-4 p-3 mt-3" style="background:rgba(6,13,170,.055);">
                                    <h6 class="fw-bold mb-2">Thông tin hoàn tiền</h6>
                                    <div class="small text-muted">
                                        <div><strong>Số tiền:</strong> {{ number_format($booking->payment->refund_amount ?? 0, 0, ',', '.') }}đ</div>
                                        <div><strong>Lý do:</strong> {{ $booking->payment->refund_reason ?: '-' }}</div>
                                        <div><strong>Ghi chú:</strong> {{ $booking->payment->refund_note ?: '-' }}</div>
                                        @if($booking->payment->refunded_at)
                                            <div><strong>Thời gian xử lý:</strong> {{ $booking->payment->refunded_at->format('d/m/Y H:i') }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @else
                            <p class="text-muted mb-0">Chưa có thanh toán.</p>
                        @endif

                        @if($canContinuePayment)
                            <a href="{{ route('customer.payments.checkout', $booking) }}" class="btn tm-btn-primary w-100 mt-3 py-3">
                                <i class="bi bi-wallet2 me-2"></i>Tiếp tục thanh toán
                            </a>
                            <div class="small text-muted mt-2">
                                Hạn giữ phòng: {{ $booking->hold_expires_at ? $booking->hold_expires_at->format('d/m/Y H:i') : '-' }}.
                                Trong thời gian này, hạng phòng đã chọn được tạm giữ để tránh khách khác đặt trùng.
                            </div>
                        @endif
                    </div>

                    <div class="tm-card p-4 mb-4" data-aos="fade-left" data-aos-delay="60">
                        <div class="tm-eyebrow"><i class="bi bi-life-preserver"></i> Hỗ trợ đặt phòng</div>
                        <div class="tm-info-list">
                            <div><span>Hotline Admin</span><strong>{{ $adminPhone }}</strong></div>
                            <div><span>Email Admin</span><strong>{{ $adminEmail }}</strong></div>
                            <div><span>Mã đơn cần cung cấp</span><strong>{{ $booking->booking_code }}</strong></div>
                            <div><span>Địa chỉ khách sạn</span><strong>{{ $booking->hotel->address ?? '-' }} @if($booking->hotel->district), {{ $booking->hotel->district }}@endif @if($booking->hotel->province), {{ $booking->hotel->province }}@endif</strong></div>
                            <div><span>SĐT khách sạn</span><strong>{{ optional($booking->hotel->owner)->phone ?: 'Chưa cập nhật' }}</strong></div>
                            <div><span>Email khách sạn</span><strong>{{ optional($booking->hotel->owner)->email ?: 'Chưa cập nhật' }}</strong></div>
                        </div>
                    </div>

                    @if($canCancelOnline)
                        <div class="tm-card p-4" data-aos="fade-left" data-aos-delay="80">
                            @if($isPaidBooking)
                                <div class="tm-eyebrow"><i class="bi bi-send-x"></i> Gửi yêu cầu hủy & hoàn tiền</div>
                                <p class="text-muted small">
                                    Đơn đã thanh toán. Bạn có thể gửi yêu cầu hủy trước ngày nhận phòng.
                                    Yêu cầu sẽ được chuyển cho Admin Travel Mate để kiểm tra chính sách hủy/hoàn tiền của khách sạn.
                                </p>
                            @else
                                <div class="tm-eyebrow"><i class="bi bi-x-circle"></i> Hủy đơn chưa thanh toán</div>
                                <p class="text-muted small">
                                    Đơn chưa thanh toán có thể hủy ngay. Sau khi hủy, phòng đang tạm giữ sẽ được giải phóng và không phát sinh hoàn tiền.
                                </p>
                            @endif

                            <div class="alert alert-light border small">
                                <strong>Chính sách hủy / hoàn tiền của khách sạn:</strong>
                                <div class="mt-1">{{ $booking->hotel->cancellation_policy ?: 'Khách sạn chưa cập nhật chính sách hủy / hoàn tiền. Vui lòng liên hệ Admin Travel Mate để được xác nhận.' }}</div>
                            </div>

                            @if($isPaidBooking)
                                <div class="rounded-4 p-3 mb-3" style="background: rgba(6,13,170,.055);">
                                    <h6 class="fw-bold mb-3"><i class="bi bi-life-preserver me-1"></i> Liên hệ Admin Travel Mate</h6>
                                    <div class="tm-info-list">
                                        <div><span>Hotline Admin</span><strong>{{ $adminPhone }}</strong></div>
                                        <div><span>Email Admin</span><strong>{{ $adminEmail }}</strong></div>
                                        <div><span>Mã đơn cần cung cấp</span><strong>{{ $booking->booking_code }}</strong></div>
                                    </div>
                                </div>

                                <div class="alert alert-warning small">
                                    Không phải mọi trường hợp đều được hoàn tiền. Admin sẽ đối chiếu ngày hủy, chính sách khách sạn và trạng thái thanh toán trước khi xác nhận hoàn tiền hoặc không hoàn tiền.
                                </div>
                            @else
                                <div class="alert alert-info small">
                                    Đơn chưa thanh toán sẽ được hủy ngay sau khi xác nhận. Không cần Admin xử lý hoàn tiền.
                                </div>
                            @endif

                            <form method="POST" action="{{ route('customer.bookings.cancel', $booking) }}" class="js-confirm-form" data-confirm="{{ $isPaidBooking ? 'Gửi yêu cầu hủy và hoàn tiền cho Admin?' : 'Xác nhận hủy đơn chưa thanh toán?' }}">
                                @csrf
                                <div class="tm-field mb-3">
                                    <label>{{ $isPaidBooking ? 'Lý do hủy / yêu cầu hoàn tiền' : 'Lý do hủy' }}</label>
                                    <textarea name="cancel_reason" class="form-control" rows="3" placeholder="Nhập lý do hủy nếu có">{{ old('cancel_reason') }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-outline-danger w-100 py-3">
                                    @if($isPaidBooking)
                                        <i class="bi bi-send-x me-2"></i>Yêu cầu hủy và Admin xem xét hoàn tiền
                                    @else
                                        <i class="bi bi-x-lg me-2"></i>Hủy đơn ngay
                                    @endif
                                </button>
                            </form>
                        </div>
                    @elseif($isCancelBlockedByDate)
                        <div class="tm-card p-4" data-aos="fade-left" data-aos-delay="80">
                            <div class="tm-eyebrow text-danger"><i class="bi bi-headset"></i> Cần hỗ trợ hủy đơn</div>
                            <p class="text-muted mb-3">
                                Đơn đã đến ngày nhận phòng nên không thể hủy trực tuyến. Vui lòng liên hệ Admin Travel Mate hoặc khách sạn để được kiểm tra chính sách xử lý.
                            </p>
                            <div class="alert alert-warning small mb-3">
                                <strong>Chính sách hủy / hoàn tiền:</strong>
                                <div class="mt-1">{{ $booking->hotel->cancellation_policy ?: 'Khách sạn chưa cập nhật chính sách hủy / hoàn tiền. Vui lòng liên hệ Admin Travel Mate để được xác nhận.' }}</div>
                            </div>
                            <div class="rounded-4 p-3 mb-3" style="background: rgba(6,13,170,.055);">
                                <h6 class="fw-bold mb-3"><i class="bi bi-life-preserver me-1"></i> Liên hệ Admin Travel Mate</h6>
                                <div class="tm-info-list">
                                    <div><span>Hotline Admin</span><strong>{{ $adminPhone }}</strong></div>
                                    <div><span>Email Admin</span><strong>{{ $adminEmail }}</strong></div>
                                    <div><span>Mã đơn cần cung cấp</span><strong>{{ $booking->booking_code }}</strong></div>
                                </div>
                            </div>
                            <div class="rounded-4 p-3" style="background: rgba(25,24,23,.045);">
                                <h6 class="fw-bold mb-3"><i class="bi bi-building me-1"></i> Thông tin khách sạn</h6>
                                <div class="tm-info-list">
                                    <div><span>Khách sạn</span><strong>{{ $booking->hotel->name }}</strong></div>
                                    <div><span>Địa chỉ</span><strong>{{ $booking->hotel->address ?? '-' }} @if($booking->hotel->district), {{ $booking->hotel->district }}@endif @if($booking->hotel->province), {{ $booking->hotel->province }}@endif</strong></div>
                                    <div><span>SĐT khách sạn</span><strong>{{ optional($booking->hotel->owner)->phone ?: 'Chưa cập nhật' }}</strong></div>
                                    <div><span>Email khách sạn</span><strong>{{ optional($booking->hotel->owner)->email ?: 'Chưa cập nhật' }}</strong></div>
                                </div>
                            </div>
                            <div class="alert alert-info small mt-3 mb-0">
                                Nếu chính sách cho phép hoàn tiền, Admin sẽ xử lý theo quy trình của Travel Mate và cập nhật kết quả trong lịch sử đặt phòng của bạn.
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
    
