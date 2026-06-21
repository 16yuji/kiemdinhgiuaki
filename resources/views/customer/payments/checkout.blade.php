@extends('layouts.frontend')

@section('title', 'Thanh toán đặt phòng')

@section('content')
<section class="tm-section pb-4">
    <div class="container">
        <div class="tm-page-hero" data-aos="fade-up">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="tm-eyebrow"><i class="bi bi-credit-card"></i> Checkout</div>
                    <h1 class="tm-heading-md mb-2">Hoàn tất thanh toán.</h1>
                    <p class="tm-lead mb-0">Chọn VNPAY sandbox hoặc thanh toán giả lập cho demo. Sau khi thành công, booking chuyển sang đã xác nhận.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="{{ route('customer.bookings.show', $booking) }}" class="btn tm-btn-light px-4 py-3">
                        <i class="bi bi-arrow-left me-2"></i>Chi tiết đơn
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="tm-section pt-0">
    <div class="container tm-payment-shell">
        <div class="row g-4 align-items-start">
            <div class="col-lg-7">
                <div class="tm-card p-4 mb-4" data-aos="fade-up">
                    <div class="tm-eyebrow"><i class="bi bi-receipt"></i> Thông tin đơn đặt phòng</div>
                    <div class="tm-meta-card">
                        <div><span>Mã đơn</span><strong>{{ $booking->booking_code }}</strong></div>
                        <div><span>Trạng thái</span><strong>@include('customer.bookings._status', ['status' => $booking->status])</strong></div>
                        <div><span>Khách sạn</span><strong>{{ $booking->hotel->name }}</strong></div>
                        <div><span>Người liên hệ</span><strong>{{ $booking->contact_name }}</strong></div>
                        <div><span>Thời gian lưu trú</span><strong>{{ $booking->checkin_date->format('d/m/Y') }} - {{ $booking->checkout_date->format('d/m/Y') }}</strong></div>
                        <div><span>Số khách</span><strong>{{ $booking->guest_count }} khách</strong></div>
                    </div>
                </div>

                @include('shared.hotel-map', [
                    'hotel' => $booking->hotel,
                    'mapId' => 'checkout-hotel-map',
                    'heading' => 'Kiem tra vi tri luu tru',
                    'class' => 'mb-4',
                ])

                <div class="tm-card p-4" data-aos="fade-up" data-aos-delay="80">
                    <div class="tm-eyebrow"><i class="bi bi-door-open"></i> Hạng phòng</div>
                    <div class="table-responsive">
                        <table class="table tm-table align-middle mb-0">
                            <thead>
                            <tr>
                                <th>Hạng phòng</th>
                                <th class="text-center">Số lượng</th>
                                <th class="text-end">Giá/đêm</th>
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
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">{{ number_format($item->price_per_night, 0, ',', '.') }}đ</td>
                                    <td class="text-end fw-bold">{{ number_format($item->subtotal, 0, ',', '.') }}đ</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="tm-payment-aside">
                    <div class="tm-transfer-card mb-4" data-aos="fade-left">
                        <span>Số tiền cần thanh toán</span>
                        <strong>{{ number_format($booking->total_amount, 0, ',', '.') }}đ</strong>
                        <div class="text-white-50 fw-bold mt-2">Đây là tổng tiền booking khách hàng thanh toán.</div>
                    </div>

                    @if($booking->hold_expires_at)
                        <div class="tm-policy-box mb-4" data-aos="fade-left" data-aos-delay="60">
                            <strong><i class="bi bi-hourglass-split me-1"></i>Hạn giữ phòng:</strong>
                            {{ $booking->hold_expires_at->format('d/m/Y H:i') }}.
                            Vui lòng hoàn tất trước thời điểm này để tránh đơn hết hạn.
                        </div>
                    @endif

                    <div class="tm-form-card" data-aos="fade-left" data-aos-delay="100">
                        <div class="tm-eyebrow"><i class="bi bi-wallet2"></i> Chọn phương thức</div>

                        <form method="POST" action="{{ route('customer.payments.vnpay', $booking) }}" class="mb-3">
                            @csrf
                            <button type="submit" class="btn tm-btn-primary w-100 py-3">
                                <i class="bi bi-credit-card-2-front me-2"></i>Thanh toán qua VNPAY
                            </button>
                        </form>

                        <form method="POST"
                              action="{{ route('customer.payments.simulate-success', $booking) }}"
                              class="js-confirm-form"
                              data-confirm="Xác nhận mô phỏng thanh toán thành công cho đơn này?">
                            @csrf
                            <button type="submit" class="btn tm-btn-light w-100 py-3">
                                <i class="bi bi-play-circle me-2"></i>Thanh toán giả lập cho demo
                            </button>
                        </form>

                        <div class="tm-neutral-box mt-4">
                            <strong>Sandbox/demo</strong>
                            <div class="small text-muted mt-1">
                                Môi trường này không trừ tiền thật. Nếu VNPAY sandbox gặp lỗi mạng hoặc callback, bạn có thể dùng thanh toán giả lập để demo nghiệp vụ.
                            </div>
                        </div>
                    </div>

                    <div class="tm-card p-4 mt-4" data-aos="fade-left" data-aos-delay="160">
                        <div class="tm-eyebrow"><i class="bi bi-credit-card"></i> Thẻ test VNPAY</div>
                        <div class="tm-info-list">
                            <div><span>Ngân hàng</span><strong>NCB</strong></div>
                            <div><span>Số thẻ</span><strong>9704198526191432198</strong></div>
                            <div><span>Chủ thẻ</span><strong>NGUYEN VAN A</strong></div>
                            <div><span>Ngày phát hành</span><strong>07/15</strong></div>
                            <div><span>OTP</span><strong>123456</strong></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
