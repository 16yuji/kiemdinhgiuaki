@extends('layouts.frontend')

@section('title', 'Thông tin thanh toán Owner')

@section('content')
<section class="tm-section">
    <div class="container">
        <div class="tm-page-toolbar">
            <div>
                <div class="tm-eyebrow"><i class="bi bi-bank"></i> Đối soát Owner</div>
                <h1 class="tm-heading-md mb-2">Thông tin thanh toán</h1>
                <p class="tm-lead mb-0">Tài khoản nhận chuyển khoản khi Admin xác nhận settlement.</p>
            </div>
            <a href="{{ route('profile.edit') }}" class="btn tm-btn-light">
                <i class="bi bi-arrow-left me-1"></i> Quay lại hồ sơ
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            </div>
        @endif

        <div class="row g-4 align-items-start">
            <div class="col-lg-7">
                <div class="tm-form-card">
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
                        <div>
                            <h2 class="h4 fw-black mb-1">Tài khoản nhận đối soát</h2>
                            <p class="text-muted fw-semibold mb-0">Thông tin này chỉ dùng cho vận hành settlement giữa Travel Mate và Owner.</p>
                        </div>
                        <span class="tm-dashboard-chip"><i class="bi bi-shield-check"></i> Nội bộ</span>
                    </div>

                    <form method="POST" action="{{ route('profile.payment-info.update') }}">
                        @csrf
                        @method('PATCH')

                        <div class="row g-3">
                            <div class="col-12">
                                <div class="tm-field">
                                    <label>Tên ngân hàng <span class="text-danger">*</span></label>
                                    <input
                                        type="text"
                                        name="bank_name"
                                        class="form-control @error('bank_name') is-invalid @enderror"
                                        value="{{ old('bank_name', $user->bank_name) }}"
                                        placeholder="Ví dụ: Vietcombank"
                                        required
                                    >
                                    @error('bank_name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="tm-field">
                                    <label>Số tài khoản <span class="text-danger">*</span></label>
                                    <input
                                        type="text"
                                        name="bank_account_number"
                                        class="form-control @error('bank_account_number') is-invalid @enderror"
                                        value="{{ old('bank_account_number', $user->bank_account_number) }}"
                                        required
                                    >
                                    @error('bank_account_number')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="tm-field">
                                    <label>Tên chủ tài khoản <span class="text-danger">*</span></label>
                                    <input
                                        type="text"
                                        name="bank_account_name"
                                        class="form-control @error('bank_account_name') is-invalid @enderror"
                                        value="{{ old('bank_account_name', $user->bank_account_name) }}"
                                        placeholder="Ví dụ: NGUYEN VAN A"
                                        required
                                    >
                                    @error('bank_account_name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-4">
                            <button class="btn tm-btn-primary" type="submit">
                                <i class="bi bi-save2 me-1"></i> Lưu thông tin thanh toán
                            </button>
                            <a href="{{ route('profile.edit') }}" class="btn tm-btn-light">
                                Hủy
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="tm-transfer-card mb-3">
                    <span>Settlement Owner</span>
                    <strong>Rõ ràng</strong>
                    <p class="mb-0 mt-2 fw-semibold text-white-50">Admin dùng thông tin này để xác nhận chuyển khoản thủ công trong môi trường demo.</p>
                </div>

                <div class="tm-policy-box mb-3">
                    <strong>Không xử lý hoàn tiền tại đây</strong>
                    <p class="mb-0 mt-2 fw-semibold">Owner chỉ xem doanh thu và khoản khấu trừ. Quyết định hoàn tiền thuộc Admin theo chính sách hủy của khách sạn.</p>
                </div>

                <div class="tm-neutral-box">
                    <h3 class="h5 fw-black mb-3"><i class="bi bi-list-check me-1 text-primary"></i> Kiểm tra trước khi lưu</h3>
                    <ul class="tm-step-list">
                        <li><i class="bi bi-check2"></i><span>Tên chủ tài khoản trùng với tài khoản nhận tiền.</span></li>
                        <li><i class="bi bi-check2"></i><span>Số tài khoản không chứa khoảng trắng hoặc ký tự lạ.</span></li>
                        <li><i class="bi bi-check2"></i><span>Thông tin ngân hàng được cập nhật trước kỳ đối soát.</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
