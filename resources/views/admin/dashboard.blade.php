@extends('layouts.dashboard')

@section('title', 'Admin Dashboard')
@section('page-title', 'Tổng quan quản trị')

@section('content')
<div class="tm-kpi-grid mb-4">
    <div class="tm-kpi-card">
        <div class="d-flex justify-content-between align-items-start gap-3">
            <div><span>Người dùng</span><strong>{{ $userCount ?? 0 }}</strong><small>Tài khoản đang có trong hệ thống.</small></div>
            <div class="tm-kpi-icon"><i class="bi bi-people"></i></div>
        </div>
    </div>
    <div class="tm-kpi-card">
        <div class="d-flex justify-content-between align-items-start gap-3">
            <div><span>Khách sạn</span><strong>{{ $hotelCount ?? 0 }}</strong><small>Cơ sở lưu trú chờ hoặc đã duyệt.</small></div>
            <div class="tm-kpi-icon"><i class="bi bi-buildings"></i></div>
        </div>
    </div>
    <div class="tm-kpi-card">
        <div class="d-flex justify-content-between align-items-start gap-3">
            <div><span>Đơn đặt phòng</span><strong>{{ $bookingCount ?? 0 }}</strong><small>Toàn bộ booking trong hệ thống.</small></div>
            <div class="tm-kpi-icon"><i class="bi bi-calendar2-check"></i></div>
        </div>
    </div>
    <div class="tm-kpi-card">
        <div class="d-flex justify-content-between align-items-start gap-3">
            <div><span>Đánh giá</span><strong>{{ $reviewCount ?? 0 }}</strong><small>Nội dung cần theo dõi chất lượng.</small></div>
            <div class="tm-kpi-icon"><i class="bi bi-star-half"></i></div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="tm-card p-4 h-100">
            <div class="tm-eyebrow"><i class="bi bi-diagram-3"></i> Quy trình vận hành</div>
            <div class="row g-3">
                <div class="col-md-6"><div class="tm-soft-panel h-100"><strong>Duyệt đối tác</strong><div class="small text-muted mt-1">Kiểm tra yêu cầu trở thành Owner trước khi mở quyền quản lý khách sạn.</div></div></div>
                <div class="col-md-6"><div class="tm-soft-panel h-100"><strong>Kiểm duyệt khách sạn</strong><div class="small text-muted mt-1">Theo dõi trạng thái active, hidden, locked và yêu cầu xem xét.</div></div></div>
                <div class="col-md-6"><div class="tm-soft-panel h-100"><strong>Hoàn tiền</strong><div class="small text-muted mt-1">Đối chiếu chính sách khách sạn và tạo clawback khi booking đã settlement.</div></div></div>
                <div class="col-md-6"><div class="tm-soft-panel h-100"><strong>Đối soát</strong><div class="small text-muted mt-1">Xác nhận số tiền thực chuyển sau khi trừ OwnerAdjustment pending.</div></div></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="tm-transfer-card h-100">
            <span>Travel Mate Admin</span>
            <strong>Kiểm soát tách biệt</strong>
            <div class="text-white-50 fw-bold mt-3">Khách hàng không thấy dữ liệu tài chính nội bộ; Owner chỉ theo dõi doanh thu và công nợ của chính mình.</div>
        </div>
    </div>
</div>
@endsection
