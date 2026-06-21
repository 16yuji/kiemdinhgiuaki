@extends('layouts.frontend')

@section('title', 'Đăng ký trở thành đối tác')

@section('content')
<section class="tm-section pb-4">
    <div class="container">
        <div class="tm-page-hero" data-aos="fade-up">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="tm-eyebrow"><i class="bi bi-handshake"></i> Partner request</div>
                    <h1 class="tm-heading-md mb-2">Đưa cơ sở lưu trú của bạn lên Travel Mate.</h1>
                    <p class="tm-lead mb-0">Admin sẽ kiểm tra thông tin trước khi chuyển tài khoản Customer thành Owner. Bạn vẫn không cần thay đổi tài khoản hiện tại.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="{{ route('customer.home') }}" class="btn tm-btn-light px-4 py-3">
                        <i class="bi bi-house-door me-2"></i>Trang chủ
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}</div>
        @endif
    </div>
</section>

<section class="tm-section pt-0">
    <div class="container">
        @if($latestRequest)
            <div class="tm-card p-4 mb-4" data-aos="fade-up">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <div class="tm-eyebrow mb-2"><i class="bi bi-clock-history"></i> Yêu cầu gần nhất</div>
                        <h2 class="h5 fw-black mb-1">{{ $latestRequest->business_name }}</h2>
                        <p class="text-muted mb-0">{{ $latestRequest->address }}</p>
                    </div>
                    <div>
                        @if($latestRequest->status === 'pending')
                            <span class="badge bg-warning text-dark">Chờ duyệt</span>
                        @elseif($latestRequest->status === 'approved')
                            <span class="badge bg-success">Đã duyệt</span>
                        @else
                            <span class="badge bg-danger">Từ chối</span>
                        @endif
                    </div>
                </div>
                @if($latestRequest->reject_reason)
                    <div class="tm-danger-box mt-3">
                        <strong>Lý do từ chối:</strong> {{ $latestRequest->reject_reason }}
                    </div>
                @endif
            </div>
        @endif

        <div class="row g-4 align-items-start">
            <div class="col-lg-7">
                <div class="tm-form-card" data-aos="fade-up">
                    <div class="tm-eyebrow"><i class="bi bi-building-add"></i> Thông tin cơ sở kinh doanh</div>
                    <form method="POST" action="{{ route('customer.partner-request.store') }}">
                        @csrf

                        <div class="tm-field mb-3">
                            <label>Tên cơ sở kinh doanh <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                name="business_name"
                                class="form-control @error('business_name') is-invalid @enderror"
                                value="{{ old('business_name') }}"
                                placeholder="Ví dụ: Travel Mate Boutique Hotel"
                                required
                            >
                            @error('business_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="tm-field mb-3">
                            <label>Địa chỉ <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                name="address"
                                class="form-control @error('address') is-invalid @enderror"
                                value="{{ old('address') }}"
                                placeholder="Số nhà, phường/xã, quận/huyện, tỉnh/thành"
                                required
                            >
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="tm-field">
                                    <label>Số điện thoại liên hệ <span class="text-danger">*</span></label>
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
                                    <label>Email liên hệ <span class="text-danger">*</span></label>
                                    <input
                                        type="email"
                                        name="contact_email"
                                        class="form-control @error('contact_email') is-invalid @enderror"
                                        value="{{ old('contact_email', auth()->user()->email) }}"
                                        required
                                    >
                                    @error('contact_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="tm-field mt-3">
                            <label>Mô tả thêm</label>
                            <textarea
                                name="description"
                                rows="5"
                                class="form-control"
                                placeholder="Mô tả ngắn về loại hình lưu trú, số phòng, khu vực hoạt động, kinh nghiệm vận hành..."
                            >{{ old('description') }}</textarea>
                        </div>

                        <button class="btn tm-btn-primary w-100 py-3 mt-4" type="submit">
                            <i class="bi bi-send-check me-2"></i>Gửi yêu cầu xét duyệt
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="tm-payment-aside">
                    <div class="tm-card p-4 mb-4" data-aos="fade-left">
                        <div class="tm-eyebrow"><i class="bi bi-list-check"></i> Quy trình duyệt đối tác</div>
                        <ol class="tm-step-list">
                            <li><i class="bi bi-1-circle"></i><div><strong>Gửi thông tin cơ sở</strong><br><span class="text-muted small">Customer điền thông tin liên hệ và mô tả vận hành.</span></div></li>
                            <li><i class="bi bi-2-circle"></i><div><strong>Admin kiểm tra</strong><br><span class="text-muted small">Travel Mate xác minh thông tin trước khi mở quyền Owner.</span></div></li>
                            <li><i class="bi bi-3-circle"></i><div><strong>Quản lý khách sạn</strong><br><span class="text-muted small">Sau khi duyệt, tài khoản có thể tạo khách sạn, hạng phòng và phòng.</span></div></li>
                        </ol>
                    </div>
                    <div class="tm-policy-box" data-aos="fade-left" data-aos-delay="80">
                        <strong><i class="bi bi-shield-check me-1"></i>Lưu ý vận hành.</strong>
                        Owner quản lý phòng, check-in, check-out và báo cáo doanh thu; Admin Travel Mate hỗ trợ kiểm duyệt và xử lý các quy trình hậu kiểm để đảm bảo minh bạch.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
