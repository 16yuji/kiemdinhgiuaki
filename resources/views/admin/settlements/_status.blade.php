@switch($status)
    @case('temporary_recorded')
        <span class="badge bg-warning text-dark">Ghi nhận tạm</span>
        @break

    @case('waiting_settlement')
        <span class="badge bg-primary">Chờ đối soát</span>
        @break

    @case('settled')
        <span class="badge bg-success">Đã đối soát</span>
        @break

    @case('adjusted')
        <span class="badge bg-secondary">Đã điều chỉnh</span>
        @break

    @case('postponed')
        <span class="badge bg-dark">Tạm hoãn</span>
        @break

    @default
        <span class="badge bg-light text-dark">{{ $status }}</span>
@endswitch