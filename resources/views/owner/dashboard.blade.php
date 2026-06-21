@extends('layouts.dashboard')

@section('title', 'Owner Dashboard')
@section('page-title', 'Tổng quan chủ cơ sở')

@section('content')
<div class="tm-kpi-grid mb-4">
    <div class="tm-kpi-card">
        <div class="d-flex justify-content-between align-items-start gap-3">
            <div><span>Khách sạn của tôi</span><strong>{{ $hotelCount ?? 0 }}</strong><small>Cơ sở lưu trú đang quản lý.</small></div>
            <div class="tm-kpi-icon"><i class="bi bi-building"></i></div>
        </div>
    </div>
    <div class="tm-kpi-card">
        <div class="d-flex justify-content-between align-items-start gap-3">
            <div><span>Hạng phòng</span><strong>{{ $roomTypeCount ?? 0 }}</strong><small>Các loại phòng đang mở bán.</small></div>
            <div class="tm-kpi-icon"><i class="bi bi-door-open"></i></div>
        </div>
    </div>
    <div class="tm-kpi-card">
        <div class="d-flex justify-content-between align-items-start gap-3">
            <div><span>Phòng vật lý</span><strong>{{ $roomCount ?? 0 }}</strong><small>Phòng available, occupied, cleaning hoặc bảo trì.</small></div>
            <div class="tm-kpi-icon"><i class="bi bi-grid-3x3-gap"></i></div>
        </div>
    </div>
    <div class="tm-kpi-card">
        <div class="d-flex justify-content-between align-items-start gap-3">
            <div><span>Đơn đặt phòng</span><strong>{{ $bookingCount ?? 0 }}</strong><small>Booking thuộc các khách sạn của bạn.</small></div>
            <div class="tm-kpi-icon"><i class="bi bi-calendar-check"></i></div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="tm-card p-4 h-100">
            <div class="tm-eyebrow"><i class="bi bi-list-check"></i> Việc cần theo dõi</div>
            <div class="row g-3">
                <div class="col-md-6"><div class="tm-soft-panel h-100"><strong>Booking hôm nay</strong><div class="small text-muted mt-1">Chỉ check-in khi booking đã xác nhận và đã đến ngày nhận phòng.</div></div></div>
                <div class="col-md-6"><div class="tm-soft-panel h-100"><strong>Phòng sẵn sàng</strong><div class="small text-muted mt-1">Đảm bảo phòng available đúng hạng trước khi gán cho khách.</div></div></div>
                <div class="col-md-6"><div class="tm-soft-panel h-100"><strong>Checkout</strong><div class="small text-muted mt-1">Phòng sau checkout chuyển sang cleaning để vận hành dọn phòng.</div></div></div>
                <div class="col-md-6"><div class="tm-soft-panel h-100"><strong>Revenue</strong><div class="small text-muted mt-1">Theo dõi settlement và các khoản khấu trừ do refund sau settlement.</div></div></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="tm-transfer-card h-100">
            <span>Owner Portal</span>
            <strong>Vận hành rõ ràng</strong>
            <div class="text-white-50 fw-bold mt-3">Owner không xử lý refund trực tiếp. Mọi quyết định hoàn tiền được Admin Travel Mate kiểm tra theo chính sách khách sạn.</div>
        </div>
    </div>
</div>
@endsection
