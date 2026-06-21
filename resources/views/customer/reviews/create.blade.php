@extends('layouts.frontend')

@section('title', 'Đánh giá dịch vụ')

@section('content')
<section class="tm-section pb-4">
    <div class="container">
        <div class="tm-page-hero" data-aos="fade-up">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="tm-eyebrow"><i class="bi bi-star"></i> Review stay</div>
                    <h1 class="tm-heading-md mb-2">Chia sẻ trải nghiệm lưu trú.</h1>
                    <p class="tm-lead mb-0">Đánh giá giúp khách hàng khác chọn nơi ở phù hợp và giúp Owner cải thiện chất lượng dịch vụ.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="{{ route('customer.bookings.show', $booking) }}" class="btn tm-btn-light px-4 py-3">
                        <i class="bi bi-arrow-left me-2"></i>Quay lại booking
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="tm-section pt-0">
    <div class="container">
        <div class="row g-4 align-items-start">
            <div class="col-lg-7">
                <div class="tm-form-card" data-aos="fade-up">
                    <div class="tm-eyebrow"><i class="bi bi-pencil-square"></i> Nội dung đánh giá</div>
                    <form method="POST" action="{{ route('customer.reviews.store', $booking) }}">
                        @csrf

                        <div class="tm-field mb-3">
                            <label>Số sao <span class="text-danger">*</span></label>
                            <select name="rating" class="form-select @error('rating') is-invalid @enderror" required>
                                <option value="">-- Chọn đánh giá --</option>
                                <option value="5" @selected(old('rating') == 5)>5 sao - Rất tốt</option>
                                <option value="4" @selected(old('rating') == 4)>4 sao - Tốt</option>
                                <option value="3" @selected(old('rating') == 3)>3 sao - Bình thường</option>
                                <option value="2" @selected(old('rating') == 2)>2 sao - Chưa tốt</option>
                                <option value="1" @selected(old('rating') == 1)>1 sao - Rất tệ</option>
                            </select>
                            @error('rating')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="tm-field mb-4">
                            <label>Nhận xét</label>
                            <textarea
                                name="comment"
                                rows="6"
                                class="form-control @error('comment') is-invalid @enderror"
                                placeholder="Ví dụ: phòng sạch, nhân viên thân thiện, vị trí thuận tiện..."
                            >{{ old('comment') }}</textarea>
                            @error('comment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn tm-btn-primary px-4 py-3" type="submit">
                                <i class="bi bi-send-check me-2"></i>Gửi đánh giá
                            </button>
                            <a href="{{ route('customer.bookings.show', $booking) }}" class="btn tm-btn-light px-4 py-3">
                                Hủy
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="tm-payment-aside">
                    <div class="tm-card p-4" data-aos="fade-left">
                        <div class="tm-eyebrow"><i class="bi bi-receipt-cutoff"></i> Booking đã hoàn tất</div>
                        <div class="tm-info-list">
                            <div><span>Mã đơn</span><strong>{{ $booking->booking_code }}</strong></div>
                            <div><span>Khách sạn</span><strong>{{ $booking->hotel->name }}</strong></div>
                            <div><span>Thời gian</span><strong>{{ $booking->checkin_date->format('d/m/Y') }} - {{ $booking->checkout_date->format('d/m/Y') }}</strong></div>
                        </div>

                        <div class="tm-neutral-box mt-3">
                            <strong>Hạng phòng đã ở</strong>
                            <div class="mt-2">
                                @foreach($booking->roomTypes as $item)
                                    <div class="d-flex justify-content-between gap-3 py-1">
                                        <span>{{ $item->roomType->name }}</span>
                                        <strong>x {{ $item->quantity }}</strong>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="tm-policy-box mt-4" data-aos="fade-left" data-aos-delay="80">
                        <strong><i class="bi bi-shield-check me-1"></i>Kiểm duyệt đánh giá.</strong>
                        Admin có thể ẩn đánh giá vi phạm, Owner chỉ được phản hồi và không thể tự xóa đánh giá của khách.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
