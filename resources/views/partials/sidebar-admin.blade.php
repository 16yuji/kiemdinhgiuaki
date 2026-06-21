@php
    $adminMenu = [
        ['route' => 'admin.dashboard', 'pattern' => 'admin.dashboard', 'icon' => 'bi-speedometer2', 'label' => 'Tổng quan'],
        ['route' => 'admin.users.index', 'pattern' => 'admin.users.*', 'icon' => 'bi-people', 'label' => 'Quản lý người dùng'],
        ['route' => 'admin.partner-requests.index', 'pattern' => 'admin.partner-requests.*', 'icon' => 'bi-person-check', 'label' => 'Duyệt đối tác'],
        ['route' => 'admin.hotels.index', 'pattern' => 'admin.hotels.*', 'icon' => 'bi-buildings', 'label' => 'Quản lý khách sạn'],
        ['route' => 'admin.hotel-appeals.index', 'pattern' => 'admin.hotel-appeals.*', 'icon' => 'bi-shield-exclamation', 'label' => 'Yêu cầu xem xét'],
        ['route' => 'admin.reviews.index', 'pattern' => 'admin.reviews.*', 'icon' => 'bi-star-half', 'label' => 'Kiểm duyệt đánh giá'],
        ['route' => 'admin.settlements.index', 'pattern' => 'admin.settlements.*', 'icon' => 'bi-cash-stack', 'label' => 'Đối soát doanh thu'],
        ['route' => 'admin.refunds.index', 'pattern' => 'admin.refunds.*', 'icon' => 'bi-arrow-counterclockwise', 'label' => 'Xử lý hoàn tiền'],
    ];
@endphp

<ul class="nav nav-pills flex-column gap-2">
    @foreach($adminMenu as $item)
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
