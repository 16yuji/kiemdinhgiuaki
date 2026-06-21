@extends('layouts.dashboard')

@section('title', 'Chi tiết đối soát')
@section('page-title', 'Chi tiết đối soát')

@section('content')
<div class="tm-page-toolbar">
    <div>
        <div class="tm-eyebrow"><i class="bi bi-cash-stack"></i> Settlement review</div>
        <h2 class="tm-heading-md mb-2">Đối soát đơn {{ $transaction->booking->booking_code }}</h2>
        <div class="tm-status-stack">
            @include('admin.settlements._status', ['status' => $transaction->status])
            <span class="tm-mini-badge"><i class="bi bi-building"></i>{{ $transaction->booking->hotel->name }}</span>
            <span class="tm-mini-badge"><i class="bi bi-person"></i>{{ $transaction->owner->name }}</span>
        </div>
    </div>

    <a href="{{ route('admin.settlements.index') }}" class="btn tm-btn-light px-4">
        <i class="bi bi-arrow-left me-2"></i>Quay lại
    </a>
</div>

<div class="row g-4">
    <div class="col-xl-7">
        <div class="tm-card p-4 mb-4" data-aos="fade-up">
            <div class="tm-eyebrow"><i class="bi bi-receipt"></i> Thông tin giao dịch</div>
            <div class="tm-meta-card">
                <div>
                    <span>Mã đơn</span>
                    <strong>{{ $transaction->booking->booking_code }}</strong>
                </div>
                <div>
                    <span>Khách hàng</span>
                    <strong>{{ $transaction->booking->customer->name }}</strong>
                </div>
                <div>
                    <span>Khách sạn</span>
                    <strong>{{ $transaction->booking->hotel->name }}</strong>
                </div>
                <div>
                    <span>Thời gian lưu trú</span>
                    <strong>{{ $transaction->booking->checkin_date->format('d/m/Y') }} - {{ $transaction->booking->checkout_date->format('d/m/Y') }}</strong>
                </div>
                <div>
                    <span>Trạng thái</span>
                    <strong>@include('admin.settlements._status', ['status' => $transaction->status])</strong>
                </div>
                <div>
                    <span>Ghi chú</span>
                    <strong>{{ $transaction->note ?: '-' }}</strong>
                </div>
            </div>
        </div>

        <div class="tm-card p-4 mb-4" data-aos="fade-up" data-aos-delay="60">
            <div class="tm-eyebrow"><i class="bi bi-calculator"></i> Breakdown chuyển tiền</div>
            <div class="tm-finance-breakdown">
                <div class="tm-finance-row">
                    <span>Tổng tiền khách thanh toán</span>
                    <strong>{{ number_format($transaction->gross_amount, 0, ',', '.') }}đ</strong>
                </div>
                <div class="tm-finance-row">
                    <span>Phí nền tảng 15%</span>
                    <strong>{{ number_format($transaction->platform_fee, 0, ',', '.') }}đ</strong>
                </div>
                <div class="tm-finance-row">
                    <span>Khoản Owner được nhận 85%</span>
                    <strong>{{ number_format($transaction->owner_amount, 0, ',', '.') }}đ</strong>
                </div>
                <div class="tm-finance-row">
                    <span>Công nợ Owner chờ trừ</span>
                    <strong class="text-danger">{{ number_format($pendingAdjustmentAmount, 0, ',', '.') }}đ</strong>
                </div>
            </div>
        </div>

        @if($pendingAdjustments->count())
            <div class="tm-card p-4 mb-4 tm-owner-adjustment" data-aos="fade-up" data-aos-delay="120">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <div class="tm-eyebrow"><i class="bi bi-exclamation-triangle"></i> Pending owner adjustments</div>
                        <h5 class="fw-bold mb-1">Các khoản sẽ trừ trong kỳ này</h5>
                        <p class="text-muted mb-0 small">
                            Các khoản này phát sinh khi Admin hoàn tiền cho khách sau khi đơn cũ đã được đối soát cho Owner.
                        </p>
                    </div>
                    <span class="badge bg-warning text-dark">{{ number_format($pendingAdjustmentAmount, 0, ',', '.') }}đ</span>
                </div>

                <div class="table-responsive">
                    <table class="table tm-table align-middle mb-0">
                        <thead>
                        <tr>
                            <th>Mã đơn phát sinh</th>
                            <th>Lý do</th>
                            <th class="text-end">Còn phải trừ</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($pendingAdjustments as $adjustment)
                            <tr>
                                <td>{{ $adjustment->booking->booking_code ?? '-' }}</td>
                                <td>{{ $adjustment->reason ?: 'Điều chỉnh công nợ Owner' }}</td>
                                <td class="text-end text-danger fw-semibold">{{ number_format($adjustment->remaining_amount, 0, ',', '.') }}đ</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($transaction->settlement)
            <div class="tm-card p-4" data-aos="fade-up" data-aos-delay="160">
                <div class="tm-eyebrow"><i class="bi bi-check2-circle"></i> Thông tin đã đối soát</div>
                <div class="tm-meta-card">
                    <div>
                        <span>Mã chuyển khoản</span>
                        <strong>{{ $transaction->settlement->transfer_code }}</strong>
                    </div>
                    <div>
                        <span>Owner đáng lẽ nhận</span>
                        <strong>{{ number_format($transaction->owner_amount, 0, ',', '.') }}đ</strong>
                    </div>
                    <div>
                        <span>Số tiền chuyển thực tế</span>
                        <strong class="text-success">{{ number_format($transaction->settlement->amount, 0, ',', '.') }}đ</strong>
                    </div>
                    <div>
                        <span>Admin xử lý</span>
                        <strong>{{ $transaction->settlement->admin->name ?? '-' }}</strong>
                    </div>
                    <div>
                        <span>Thời gian đối soát</span>
                        <strong>{{ $transaction->settlement->settled_at?->format('d/m/Y H:i') }}</strong>
                    </div>
                    <div>
                        <span>Ghi chú</span>
                        <strong>{!! nl2br(e($transaction->settlement->note ?: '-')) !!}</strong>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="col-xl-5">
        <div class="tm-payment-aside">
            <div class="tm-transfer-card mb-4" data-aos="fade-left">
                <span>Số tiền dự kiến chuyển sau khấu trừ</span>
                <strong>{{ number_format($estimatedTransferAmount, 0, ',', '.') }}đ</strong>
                <div class="text-white-50 fw-bold mt-2">
                    Owner amount {{ number_format($transaction->owner_amount, 0, ',', '.') }}đ
                    @if($pendingAdjustmentAmount > 0)
                        - deduction {{ number_format($pendingAdjustmentAmount, 0, ',', '.') }}đ
                    @endif
                </div>
            </div>

            <div class="tm-form-card mb-4" data-aos="fade-left" data-aos-delay="80">
                <div class="tm-eyebrow"><i class="bi bi-bank"></i> Thông tin nhận tiền Owner</div>
                <div class="tm-info-list">
                    <div><span>Owner</span><strong>{{ $transaction->owner->name }}</strong></div>
                    <div><span>Email</span><strong>{{ $transaction->owner->email }}</strong></div>
                    <div><span>Ngân hàng</span><strong>{{ $transaction->owner->bank_name ?: 'Chưa cập nhật' }}</strong></div>
                    <div><span>Số tài khoản</span><strong>{{ $transaction->owner->bank_account_number ?: 'Chưa cập nhật' }}</strong></div>
                    <div><span>Chủ tài khoản</span><strong>{{ $transaction->owner->bank_account_name ?: 'Chưa cập nhật' }}</strong></div>
                </div>
            </div>

            @if(in_array($transaction->status, ['waiting_settlement', 'temporary_recorded'], true))
                <div class="tm-form-card" data-aos="fade-left" data-aos-delay="120">
                    <div class="tm-eyebrow"><i class="bi bi-send-check"></i> Xác nhận chuyển tiền</div>

                    @if(!$transaction->owner->bank_name || !$transaction->owner->bank_account_number || !$transaction->owner->bank_account_name)
                        <div class="tm-policy-box mb-3">
                            Owner chưa cập nhật đủ thông tin ngân hàng. Không nên xác nhận quyết toán trước khi Owner bổ sung.
                        </div>
                    @endif

                    @if($pendingAdjustmentAmount > 0)
                        <div class="tm-danger-box mb-3">
                            Owner đang có công nợ cần trừ:
                            <strong>{{ number_format($pendingAdjustmentAmount, 0, ',', '.') }}đ</strong>.
                            Số tiền chuyển thực tế dự kiến còn:
                            <strong>{{ number_format($estimatedTransferAmount, 0, ',', '.') }}đ</strong>.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.settlements.confirm', $transaction) }}" class="js-confirm-form" data-confirm="Xác nhận đối soát và chuyển tiền thực tế cho Owner?">
                        @csrf

                        <div class="tm-field mb-3">
                            <label>Mã giao dịch chuyển khoản <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                name="transfer_code"
                                class="form-control @error('transfer_code') is-invalid @enderror"
                                value="{{ old('transfer_code') }}"
                                placeholder="Ví dụ: VCB202605160001"
                                required
                            >
                            @error('transfer_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="tm-field mb-3">
                            <label>Ghi chú</label>
                            <textarea
                                name="note"
                                class="form-control"
                                rows="3"
                                placeholder="Ghi chú nội bộ nếu có"
                            >{{ old('note') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-3">
                            <i class="bi bi-check2-circle me-2"></i>Xác nhận đối soát
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
