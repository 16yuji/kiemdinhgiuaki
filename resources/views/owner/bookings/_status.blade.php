@switch($status)
    @case('pending_payment')
        <span class="badge bg-warning text-dark">Chờ thanh toán</span>
        @break
    @case('payment_expired')
        <span class="badge bg-secondary">Hết hạn thanh toán</span>
        @break
    @case('payment_failed')
        <span class="badge bg-danger">Thanh toán thất bại</span>
        @break
    @case('confirmed')
        <span class="badge bg-success">Đã xác nhận</span>
        @break
    @case('staying')
        <span class="badge bg-primary">Đang lưu trú</span>
        @break
    @case('completed')
        <span class="badge bg-info text-dark">Hoàn tất</span>
        @break
    @case('cancelled')
        <span class="badge bg-danger">Đã hủy</span>
        @break
    @case('no_show')
        <span class="badge bg-dark">No-show</span>
        @break
    @case('manual_review')
        <span class="badge bg-secondary">Cần xử lý</span>
        @break
    @default
        <span class="badge bg-light text-dark">{{ $status }}</span>
@endswitch