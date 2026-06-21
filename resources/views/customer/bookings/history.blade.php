@extends('layouts.frontend')

@section('title', 'Lịch sử đặt phòng')

@section('content')
<section class="tm-section pb-4">
    <div class="container">
        <div class="tm-page-hero" data-aos="fade-up">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="tm-eyebrow"><i class="bi bi-suitcase2"></i> Lịch sử đặt phòng</div>
                    <h1 class="tm-heading-md mb-2">Theo dõi hành trình của bạn.</h1>
                    <p class="tm-lead mb-0">Xem trạng thái đơn, tiếp tục thanh toán, hủy đơn hợp lệ hoặc đánh giá sau khi lưu trú hoàn tất.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="{{ route('customer.hotels.index') }}" class="btn tm-btn-primary px-4 py-3">
                        <i class="bi bi-search me-2"></i>Tìm khách sạn
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="tm-section pt-0">
    <div class="container">
        <div class="tm-card p-4 mb-4" data-aos="fade-up">
            <form method="GET" action="{{ route('customer.bookings.history') }}" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <div class="tm-field">
                        <label>Từ khóa</label>
                        <input type="text" name="keyword" class="form-control" value="{{ $keyword }}" placeholder="Mã đơn hoặc tên khách sạn">
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="tm-field">
                        <label>Trạng thái đơn</label>
                        <select name="status" class="form-select">
                            <option value="">Tất cả trạng thái</option>
                            <option value="pending_payment" @selected($status === 'pending_payment')>Chờ thanh toán</option>
                            <option value="payment_expired" @selected($status === 'payment_expired')>Hết hạn thanh toán</option>
                            <option value="payment_failed" @selected($status === 'payment_failed')>Thanh toán thất bại</option>
                            <option value="confirmed" @selected($status === 'confirmed')>Đã xác nhận</option>
                            <option value="staying" @selected($status === 'staying')>Đang lưu trú</option>
                            <option value="completed" @selected($status === 'completed')>Hoàn tất</option>
                            <option value="cancelled" @selected($status === 'cancelled')>Đã hủy</option>
                            <option value="no_show" @selected($status === 'no_show')>No-show</option>
                            <option value="manual_review" @selected($status === 'manual_review')>Cần xử lý thủ công</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button class="btn tm-btn-primary w-100"><i class="bi bi-funnel me-1"></i>Lọc</button>
                    <a href="{{ route('customer.bookings.history') }}" class="btn tm-btn-light">Xóa</a>
                </div>
            </form>
        </div>

        <div class="tm-card p-0 overflow-hidden" data-aos="fade-up" data-aos-delay="80">
            <div class="table-responsive">
                <table class="table tm-table table-hover align-middle mb-0">
                    <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách sạn</th>
                        <th>Thời gian</th>
                        <th>Tổng tiền</th>
                        <th>Thanh toán</th>
                        <th>Trạng thái</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($bookings as $booking)
                        @php
                            $canCancel = in_array($booking->status, ['pending_payment', 'confirmed'], true) && $booking->checkin_date->isFuture();
                            $canPay = $booking->status === 'pending_payment';
                            $canReview = $booking->status === 'completed' && !$booking->review;
                            $isPaidBooking = optional($booking->payment)->status === 'paid';
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $booking->booking_code }}</div>
                                <div class="text-muted small">{{ $booking->created_at->format('d/m/Y H:i') }}</div>
                            </td>
                            <td>
                                <div class="fw-bold">{{ $booking->hotel->name }}</div>
                                <div class="text-muted small">{{ $booking->hotel->district }}, {{ $booking->hotel->province }}</div>
                            </td>
                            <td>
                                <div>{{ $booking->checkin_date->format('d/m/Y') }}</div>
                                <div class="text-muted small">đến {{ $booking->checkout_date->format('d/m/Y') }}</div>
                            </td>
                            <td class="fw-bold">{{ number_format($booking->total_amount, 0, ',', '.') }}đ</td>
                            <td>
                                @if($booking->payment)
                                    @switch($booking->payment->status)
                                        @case('pending') <span class="badge bg-warning text-dark">Chờ thanh toán</span> @break
                                        @case('paid') <span class="badge bg-success">Đã thanh toán</span> @break
                                        @case('failed') <span class="badge bg-danger">Thất bại</span> @break
                                        @case('refunding') <span class="badge bg-warning text-dark">Chờ hoàn tiền</span> @break
                                        @case('refunded') <span class="badge bg-primary">Đã hoàn tiền</span> @break
                                        @case('non_refundable') <span class="badge bg-danger">Không hoàn tiền</span> @break
                                        @default <span class="badge bg-secondary">{{ $booking->payment->status }}</span>
                                    @endswitch
                                @else
                                    <span class="text-muted">Chưa có</span>
                                @endif
                            </td>
                            <td>@include('customer.bookings._status', ['status' => $booking->status])</td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end flex-wrap gap-2">
                                    <a href="{{ route('customer.bookings.show', $booking) }}" class="btn btn-sm tm-btn-light">Xem</a>

                                    @if($canPay)
                                        <a href="{{ route('customer.payments.checkout', $booking) }}" class="btn btn-sm tm-btn-primary">Thanh toán</a>
                                    @endif

                                    @if($canCancel)
                                        <form method="POST" action="{{ route('customer.bookings.cancel', $booking) }}" class="js-confirm-form" data-confirm="{{ $isPaidBooking ? 'Gửi yêu cầu hủy và hoàn tiền để Admin xem xét?' : 'Hủy đơn chưa thanh toán ngay bây giờ?' }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                {{ $isPaidBooking ? 'Yêu cầu hủy' : 'Hủy ngay' }}
                                            </button>
                                        </form>
                                    @endif

                                    @if($canReview)
                                        <a href="{{ route('customer.reviews.create', $booking) }}" class="btn btn-sm btn-warning">Đánh giá</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-suitcase2 fs-2 d-block mb-2"></i>
                                Chưa có đơn đặt phòng nào phù hợp.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $bookings->links() }}
        </div>
    </div>
</section>
@endsection
