@extends('layouts.dashboard')

@section('title', 'Quản lý khách sạn')
@section('page-title', 'Quản lý khách sạn')

@section('content')
<div class="tm-page-toolbar">
    <div>
        <div class="tm-eyebrow"><i class="bi bi-buildings"></i> Danh mục lưu trú</div>
        <h4 class="fw-black mb-1">Khách sạn của bạn</h4>
        <p class="text-muted fw-semibold mb-0">Quản lý thông tin cơ sở, ảnh đại diện, trạng thái và điểm đánh giá.</p>
    </div>

    <a href="{{ route('owner.hotels.create') }}" class="btn tm-btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Thêm khách sạn
    </a>
</div>

<div class="tm-surface p-3 p-lg-4">
    <div class="table-responsive">
        <table class="table tm-table align-middle">
            <thead>
            <tr>
                <th>Khách sạn</th>
                <th>Địa chỉ</th>
                <th>Trạng thái</th>
                <th>Đánh giá</th>
                <th class="text-end">Thao tác</th>
            </tr>
            </thead>

            <tbody>
            @forelse($hotels as $hotel)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            @if($hotel->thumbnail)
                                <img src="{{ asset('storage/' . $hotel->thumbnail) }}" class="tm-admin-thumb" alt="{{ $hotel->name }}">
                            @else
                                <div class="tm-admin-thumb tm-admin-thumb-empty"><i class="bi bi-image"></i></div>
                            @endif
                            <div>
                                <div class="fw-black">{{ $hotel->name }}</div>
                                <div class="text-muted small">
                                    Check-in {{ $hotel->checkin_time ? \Carbon\Carbon::parse($hotel->checkin_time)->format('H:i') : '-' }}
                                    · Check-out {{ $hotel->checkout_time ? \Carbon\Carbon::parse($hotel->checkout_time)->format('H:i') : '-' }}
                                </div>
                            </div>
                        </div>
                    </td>

                    <td>
                        <div class="fw-semibold">{{ $hotel->address }}</div>
                        <div class="text-muted small">{{ $hotel->ward }} {{ $hotel->district }} {{ $hotel->province }}</div>
                    </td>

                    <td>
                        @if($hotel->status === 'active')
                            <span class="tm-status tm-status-success">Hoạt động</span>
                        @elseif($hotel->status === 'hidden')
                            <span class="tm-status tm-status-warning">Đang ẩn</span>
                        @else
                            <span class="tm-status tm-status-danger">Bị khóa</span>
                        @endif
                    </td>

                    <td>
                        <strong><i class="bi bi-star-fill text-warning me-1"></i>{{ number_format($hotel->average_rating, 1) }}/5</strong>
                        <div class="text-muted small">{{ $hotel->review_count }} đánh giá</div>
                    </td>

                    <td class="text-end">
                        <div class="tm-action-stack">
                            <a href="{{ route('owner.hotels.show', $hotel) }}" class="btn btn-sm tm-btn-light">
                                <i class="bi bi-eye me-1"></i> Xem
                            </a>
                            <a href="{{ route('owner.hotels.edit', $hotel) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil-square me-1"></i> Sửa
                            </a>
                            <form
                                action="{{ route('owner.hotels.destroy', $hotel) }}"
                                method="POST"
                                class="d-inline"
                                onsubmit="return confirm('Bạn có chắc muốn xóa khách sạn này?')"
                            >
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" type="submit">
                                    <i class="bi bi-trash me-1"></i> Xóa
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        <div class="tm-empty-state">
                            <i class="bi bi-building-add"></i>
                            Bạn chưa có khách sạn nào.
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $hotels->links() }}
    </div>
</div>
@endsection
