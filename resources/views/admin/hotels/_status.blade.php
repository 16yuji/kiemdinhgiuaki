@switch($status)
    @case('active')
        <span class="badge bg-success">Hoạt động</span>
        @break

    @case('hidden')
        <span class="badge bg-warning text-dark">Đang ẩn</span>
        @break

    @case('locked')
        <span class="badge bg-danger">Bị khóa</span>
        @break

    @default
        <span class="badge bg-secondary">{{ $status }}</span>
@endswitch