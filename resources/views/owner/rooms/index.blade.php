@extends('layouts.dashboard')

@section('title', 'Quản lý phòng')
@section('page-title', 'Quản lý phòng')

@section('content')
<div class="tm-page-toolbar">
    <div>
        <div class="tm-eyebrow"><i class="bi bi-grid-3x3-gap"></i> Phòng vật lý</div>
        <h4 class="fw-black mb-1">Danh sách phòng</h4>
        <p class="text-muted fw-semibold mb-0">Theo dõi trạng thái sẵn sàng, đang ở, dọn phòng, bảo trì hoặc tạm khóa.</p>
    </div>

    <a href="{{ route('owner.rooms.create') }}" class="btn tm-btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Thêm phòng
    </a>
</div>

<div class="tm-surface p-3 p-lg-4">
    <div class="table-responsive">
        <table class="table tm-table align-middle">
            <thead>
            <tr>
                <th>Số phòng</th>
                <th>Khách sạn</th>
                <th>Hạng phòng</th>
                <th>Tầng</th>
                <th>Trạng thái</th>
                <th>Ghi chú</th>
                <th class="text-end">Thao tác</th>
            </tr>
            </thead>

            <tbody>
            @forelse($rooms as $room)
                <tr>
                    <td>
                        <span class="tm-room-number">{{ $room->room_number }}</span>
                    </td>

                    <td>{{ $room->hotel->name }}</td>
                    <td>{{ $room->roomType->name }}</td>
                    <td>{{ $room->floor ?: '-' }}</td>

                    <td>
                        @switch($room->status)
                            @case('available')
                                <span class="tm-status tm-status-success">Sẵn sàng</span>
                                @break
                            @case('occupied')
                                <span class="tm-status tm-status-primary">Đang sử dụng</span>
                                @break
                            @case('cleaning')
                                <span class="tm-status tm-status-warning">Đang dọn</span>
                                @break
                            @case('maintenance')
                                <span class="tm-status tm-status-danger">Bảo trì</span>
                                @break
                            @case('locked')
                                <span class="tm-status tm-status-muted">Tạm khóa</span>
                                @break
                            @default
                                <span class="tm-status tm-status-muted">Không rõ</span>
                        @endswitch
                    </td>

                    <td>{{ \Illuminate\Support\Str::limit($room->note, 44) ?: '-' }}</td>

                    <td class="text-end">
                        <div class="tm-action-stack">
                            <a href="{{ route('owner.rooms.show', $room) }}" class="btn btn-sm tm-btn-light">
                                <i class="bi bi-eye me-1"></i> Xem
                            </a>
                            <a href="{{ route('owner.rooms.edit', $room) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil-square me-1"></i> Sửa
                            </a>
                            <form
                                action="{{ route('owner.rooms.destroy', $room) }}"
                                method="POST"
                                class="d-inline"
                                onsubmit="return confirm('Bạn có chắc muốn xóa phòng này?')"
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
                            <i class="bi bi-grid-3x3-gap"></i>
                            Chưa có phòng nào.
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $rooms->links() }}
    </div>
</div>
@endsection
