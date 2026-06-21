@php
    $statusMap = [
        'pending_payment' => ['class' => 'tm-status-warning', 'label' => 'Chờ thanh toán'],
        'payment_expired' => ['class' => 'tm-status-muted', 'label' => 'Hết hạn thanh toán'],
        'payment_failed' => ['class' => 'tm-status-danger', 'label' => 'Thanh toán thất bại'],
        'confirmed' => ['class' => 'tm-status-success', 'label' => 'Đã xác nhận'],
        'staying' => ['class' => 'tm-status-primary', 'label' => 'Đang lưu trú'],
        'completed' => ['class' => 'tm-status-info', 'label' => 'Hoàn tất'],
        'cancelled' => ['class' => 'tm-status-danger', 'label' => 'Đã hủy'],
        'no_show' => ['class' => 'tm-status-dark', 'label' => 'No-show'],
        'manual_review' => ['class' => 'tm-status-muted', 'label' => 'Cần xử lý'],
    ];
    $item = $statusMap[$status] ?? ['class' => 'tm-status-muted', 'label' => $status];
@endphp

<span class="tm-status {{ $item['class'] }}">{{ $item['label'] }}</span>
