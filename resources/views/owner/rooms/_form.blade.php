@csrf

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Khách sạn <span class="text-danger">*</span></label>
        <select name="hotel_id" class="form-select @error('hotel_id') is-invalid @enderror" required>
            <option value="">-- Chọn khách sạn --</option>
            @foreach($hotels as $hotel)
                <option value="{{ $hotel->id }}"
                    @selected((int) old('hotel_id', $room->hotel_id ?? '') === (int) $hotel->id)>
                    {{ $hotel->name }}
                </option>
            @endforeach
        </select>
        @error('hotel_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror

        @if($hotels->isEmpty())
            <div class="form-text text-danger">
                Bạn cần tạo khách sạn trước khi thêm phòng.
            </div>
        @endif
    </div>

    <div class="col-md-6">
        <label class="form-label">Hạng phòng <span class="text-danger">*</span></label>
        <select name="room_type_id" class="form-select @error('room_type_id') is-invalid @enderror" required>
            <option value="">-- Chọn hạng phòng --</option>
            @foreach($roomTypes as $roomType)
                <option value="{{ $roomType->id }}"
                    @selected((int) old('room_type_id', $room->room_type_id ?? '') === (int) $roomType->id)>
                    {{ $roomType->name }} - {{ $roomType->hotel->name }}
                </option>
            @endforeach
        </select>
        @error('room_type_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror

        @if($roomTypes->isEmpty())
            <div class="form-text text-danger">
                Bạn cần tạo hạng phòng trước khi thêm phòng.
            </div>
        @endif
    </div>

    <div class="col-md-4">
        <label class="form-label">Số phòng <span class="text-danger">*</span></label>
        <input
            type="text"
            name="room_number"
            class="form-control @error('room_number') is-invalid @enderror"
            value="{{ old('room_number', $room->room_number ?? '') }}"
            placeholder="Ví dụ: 101, 202, A301"
            required
        >
        @error('room_number')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Tầng</label>
        <input
            type="text"
            name="floor"
            class="form-control"
            value="{{ old('floor', $room->floor ?? '') }}"
            placeholder="Ví dụ: 1, 2, 3"
        >
    </div>

    <div class="col-md-4">
        <label class="form-label">Trạng thái <span class="text-danger">*</span></label>
        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
            @php
                $currentStatus = old('status', $room->status ?? 'available');
            @endphp

            <option value="available" @selected($currentStatus === 'available')>
                Sẵn sàng
            </option>
            <option value="occupied" @selected($currentStatus === 'occupied')>
                Đang sử dụng
            </option>
            <option value="cleaning" @selected($currentStatus === 'cleaning')>
                Đang dọn
            </option>
            <option value="maintenance" @selected($currentStatus === 'maintenance')>
                Bảo trì
            </option>
            <option value="locked" @selected($currentStatus === 'locked')>
                Tạm khóa
            </option>
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Ghi chú</label>
        <textarea
            name="note"
            rows="4"
            class="form-control"
            placeholder="Ghi chú nội bộ về phòng"
        >{{ old('note', $room->note ?? '') }}</textarea>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary">
        Lưu thông tin
    </button>

    <a href="{{ route('owner.rooms.index') }}" class="btn btn-outline-secondary">
        Quay lại
    </a>
</div>