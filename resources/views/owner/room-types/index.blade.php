@extends('layouts.dashboard')

@section('title', 'Quản lý hạng phòng')
@section('page-title', 'Quản lý hạng phòng')

@section('content')
<div class="tm-page-toolbar">
    <div>
        <div class="tm-eyebrow"><i class="bi bi-door-open"></i> Sản phẩm lưu trú</div>
        <h4 class="fw-black mb-1">Hạng phòng đang bán</h4>
        <p class="text-muted fw-semibold mb-0">Cấu hình sức chứa, giá theo đêm, tiện nghi và trạng thái bán.</p>
    </div>

    <a href="{{ route('owner.room-types.create') }}" class="btn tm-btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Thêm hạng phòng
    </a>
</div>

<div class="tm-surface p-3 p-lg-4">
    <div class="table-responsive">
        <table class="table tm-table align-middle">
            <thead>
            <tr>
                <th>Hạng phòng</th>
                <th>Khách sạn</th>
                <th>Giá/đêm</th>
                <th>Sức chứa</th>
                <th>Phòng vật lý</th>
                <th>Trạng thái</th>
                <th class="text-end">Thao tác</th>
            </tr>
            </thead>

            <tbody>
            @forelse($roomTypes as $roomType)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            @if($roomType->thumbnail)
                                <img src="{{ asset('storage/' . $roomType->thumbnail) }}" class="tm-admin-thumb" alt="{{ $roomType->name }}">
                            @else
                                <div class="tm-admin-thumb tm-admin-thumb-empty"><i class="bi bi-door-open"></i></div>
                            @endif
                            <div>
                                <div class="fw-black">{{ $roomType->name }}</div>
                                <div class="text-muted small">{{ $roomType->bed_type ?: 'Chưa nhập loại giường' }}</div>
                            </div>
                        </div>
                    </td>

                    <td>{{ $roomType->hotel->name }}</td>
                    <td><strong class="text-primary">{{ number_format($roomType->price_per_night, 0, ',', '.') }}đ</strong></td>
                    <td>{{ $roomType->max_guests }} khách</td>
                    <td>{{ $roomType->rooms->count() }} phòng</td>

                    <td>
                        @if($roomType->status === 'active')
                            <span class="tm-status tm-status-success">Đang bán</span>
                        @else
                            <span class="tm-status tm-status-muted">Tạm ẩn</span>
                        @endif
                    </td>

                    <td class="text-end">
                        <div class="tm-action-stack">
                            <a href="{{ route('owner.room-types.show', $roomType) }}" class="btn btn-sm tm-btn-light">
                                <i class="bi bi-eye me-1"></i> Xem
                            </a>
                            <a href="{{ route('owner.room-types.edit', $roomType) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil-square me-1"></i> Sửa
                            </a>
                            <form
                                action="{{ route('owner.room-types.destroy', $roomType) }}"
                                method="POST"
                                class="d-inline"
                                onsubmit="return confirm('Bạn có chắc muốn xóa hạng phòng này?')"
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
                    <td colspan="7">
                        <div class="tm-empty-state">
                            <i class="bi bi-door-open"></i>
                            Chưa có hạng phòng nào.
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $roomTypes->links() }}
    </div>
</div>
@endsection
