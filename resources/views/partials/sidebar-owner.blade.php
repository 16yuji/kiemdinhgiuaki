@php
    $ownerMenu = [
        ['route' => 'owner.dashboard', 'pattern' => 'owner.dashboard', 'icon' => 'bi-speedometer2', 'label' => 'Tổng quan'],
        ['route' => 'owner.hotels.index', 'pattern' => 'owner.hotels.*', 'icon' => 'bi-building', 'label' => 'Quản lý khách sạn'],
        ['route' => 'owner.room-types.index', 'pattern' => 'owner.room-types.*', 'icon' => 'bi-door-open', 'label' => 'Quản lý hạng phòng'],
        ['route' => 'owner.rooms.index', 'pattern' => 'owner.rooms.*', 'icon' => 'bi-grid-3x3-gap', 'label' => 'Quản lý phòng'],
        ['route' => 'owner.bookings.index', 'pattern' => 'owner.bookings.*', 'icon' => 'bi-calendar-check', 'label' => 'Quản lý đơn đặt phòng'],
        ['route' => 'owner.reviews.index', 'pattern' => 'owner.reviews.*', 'icon' => 'bi-chat-heart', 'label' => 'Đánh giá & phản hồi'],
        ['route' => 'owner.revenues.index', 'pattern' => 'owner.revenues.*', 'icon' => 'bi-cash-coin', 'label' => 'Doanh thu & đối soát'],
    ];
@endphp

<ul class="nav nav-pills flex-column gap-2">
    @foreach($ownerMenu as $item)
        <li class="nav-item">
            <a href="{{ route($item['route']) }}" class="nav-link text-white {{ request()->routeIs($item['pattern']) ? 'active' : '' }}">
                <i class="bi {{ $item['icon'] }} me-2"></i>
                <span>{{ $item['label'] }}</span>
            </a>
        </li>
    @endforeach

    <li class="nav-item mt-3 pt-3 border-top border-white border-opacity-10">
        <a href="{{ route('customer.home') }}" class="nav-link text-white">
            <i class="bi bi-globe2 me-2"></i>
            <span>Xem website</span>
        </a>
    </li>
</ul>
