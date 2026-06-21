@extends('layouts.frontend')

@section('title', 'Tạo đơn đặt phòng')

@section('content')
<section class="tm-section pb-4">
    <div class="container">
        <div class="tm-page-hero" data-aos="fade-up">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="tm-eyebrow"><i class="bi bi-calendar2-plus"></i> Tạo booking</div>
                    <h1 class="tm-heading-md mb-2">Xác nhận thông tin lưu trú.</h1>
                    <p class="tm-lead mb-0">Travel Mate sẽ giữ phòng trong 15 phút sau khi tạo đơn. Hoàn tất thanh toán để xác nhận booking.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="{{ route('customer.hotels.show', [
                        'hotel' => $hotel,
                        'checkin_date' => $checkinDate,
                        'checkout_date' => $checkoutDate,
                        'guests' => $guests,
                    ]) }}" class="btn tm-btn-light px-4 py-3">
                        <i class="bi bi-arrow-left me-2"></i>Quay lại khách sạn
                    </a>
                </div>
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            </div>
        @endif
    </div>
</section>

<section class="tm-section pt-0">
    <div class="container">
        <div class="row g-4 align-items-start">
            <div class="col-lg-7">
                <div class="tm-form-card" data-aos="fade-up">
                    <div class="tm-eyebrow"><i class="bi bi-person-lines-fill"></i> Thông tin liên hệ</div>
                    <form action="{{ route('customer.bookings.store') }}" method="POST">
                        @csrf

                        <input type="hidden" name="hotel_id" value="{{ $hotel->id }}">
                        <input type="hidden" name="room_type_id" value="{{ $roomType->id }}">
                        <input type="hidden" name="checkin_date" value="{{ $checkinDate }}">
                        <input type="hidden" name="checkout_date" value="{{ $checkoutDate }}">
                        <input type="hidden" name="guest_count" value="{{ $guests }}">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="tm-field">
                                    <label>Họ tên người liên hệ <span class="text-danger">*</span></label>
                                    <input
                                        type="text"
                                        name="contact_name"
                                        class="form-control @error('contact_name') is-invalid @enderror"
                                        value="{{ old('contact_name', auth()->user()->name) }}"
                                        required
                                    >
                                    @error('contact_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="tm-field">
                                    <label>Số điện thoại <span class="text-danger">*</span></label>
                                    <input
                                        type="text"
                                        name="contact_phone"
                                        class="form-control @error('contact_phone') is-invalid @enderror"
                                        value="{{ old('contact_phone', auth()->user()->phone) }}"
                                        required
                                    >
                                    @error('contact_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="tm-field">
                                    <label>Email</label>
                                    <input
                                        type="email"
                                        name="contact_email"
                                        class="form-control @error('contact_email') is-invalid @enderror"
                                        value="{{ old('contact_email', auth()->user()->email) }}"
                                    >
                                    @error('contact_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="tm-field">
                                    <label>Số lượng phòng <span class="text-danger">*</span></label>
                                    <input
                                        type="number"
                                        name="quantity"
                                        min="1"
                                        max="{{ $availableCount }}"
                                        class="form-control @error('quantity') is-invalid @enderror"
                                        value="{{ old('quantity', 1) }}"
                                        required
                                    >
                                    <div class="form-text">Còn {{ $availableCount }} phòng khả dụng cho ngày đã chọn.</div>
                                    @error('quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="tm-field">
                                    <label>Yêu cầu đặc biệt</label>
                                    <textarea
                                        name="special_request"
                                        class="form-control"
                                        rows="4"
                                        placeholder="Ví dụ: muốn phòng tầng cao, gần thang máy, đến muộn..."
                                    >{{ old('special_request') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="tm-policy-box mt-4">
                            <strong><i class="bi bi-clock-history me-1"></i>Giữ phòng tạm thời.</strong>
                            Sau khi tạo đơn, booking ở trạng thái chờ thanh toán và giữ phòng trong 15 phút. Nếu hết hạn, phòng sẽ được mở bán lại.
                        </div>

                        <button class="btn tm-btn-primary w-100 py-3 mt-4" type="submit">
                            <i class="bi bi-wallet2 me-2"></i>Tạo đơn và đi tới thanh toán
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="tm-payment-aside">
                    <div class="tm-card p-4 mb-4" data-aos="fade-left">
                        <div class="tm-card-media rounded-4 mb-3">
                            @if($roomType->thumbnail)
                                <img src="{{ asset('storage/' . $roomType->thumbnail) }}" alt="{{ $roomType->name }}">
                            @elseif($hotel->thumbnail)
                                <img src="{{ asset('storage/' . $hotel->thumbnail) }}" alt="{{ $hotel->name }}">
                            @else
                                <div class="tm-gallery-placeholder h-100">Travel Mate</div>
                            @endif
                        </div>
                        <div class="tm-eyebrow"><i class="bi bi-buildings"></i> Tóm tắt lưu trú</div>
                        <h2 class="h4 fw-black mb-1">{{ $hotel->name }}</h2>
                        <p class="text-muted small mb-3"><i class="bi bi-geo-alt me-1"></i>{{ $hotel->address }}, {{ $hotel->district }}, {{ $hotel->province }}</p>

                        <div class="tm-room-summary">
                            <div class="tm-room-summary-item"><span>Hạng phòng</span><strong>{{ $roomType->name }}</strong></div>
                            <div class="tm-room-summary-item"><span>Nhận phòng</span><strong>{{ \Carbon\Carbon::parse($checkinDate)->format('d/m/Y') }}</strong></div>
                            <div class="tm-room-summary-item"><span>Trả phòng</span><strong>{{ \Carbon\Carbon::parse($checkoutDate)->format('d/m/Y') }}</strong></div>
                            <div class="tm-room-summary-item"><span>Số đêm</span><strong>{{ $nights }}</strong></div>
                            <div class="tm-room-summary-item"><span>Số khách</span><strong>{{ $guests }}</strong></div>
                            <div class="tm-room-summary-item"><span>Giá mỗi đêm</span><strong>{{ number_format($roomType->price_per_night, 0, ',', '.') }}đ</strong></div>
                        </div>
                    </div>

                    <div class="tm-transfer-card" data-aos="fade-left" data-aos-delay="80">
                        <span>Tạm tính cho 1 phòng</span>
                        <strong>{{ number_format($roomType->price_per_night * $nights, 0, ',', '.') }}đ</strong>
                        <div class="text-white-50 fw-bold mt-2">Tổng tiền cuối cùng thay đổi theo số lượng phòng bạn nhập trước khi tạo đơn.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
