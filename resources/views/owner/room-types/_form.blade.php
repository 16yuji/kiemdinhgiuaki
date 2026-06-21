@csrf

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Khách sạn <span class="text-danger">*</span></label>
        <select name="hotel_id" class="form-select @error('hotel_id') is-invalid @enderror" required>
            <option value="">-- Chọn khách sạn --</option>
            @foreach($hotels as $hotel)
                <option value="{{ $hotel->id }}"
                    @selected((int) old('hotel_id', $roomType->hotel_id ?? '') === (int) $hotel->id)>
                    {{ $hotel->name }}
                </option>
            @endforeach
        </select>
        @error('hotel_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror

        @if($hotels->isEmpty())
            <div class="form-text text-danger">
                Bạn cần tạo khách sạn trước khi thêm hạng phòng.
            </div>
        @endif
    </div>

    <div class="col-md-6">
        <label class="form-label">Tên hạng phòng <span class="text-danger">*</span></label>
        <input
            type="text"
            name="name"
            class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', $roomType->name ?? '') }}"
            placeholder="Ví dụ: Deluxe, Standard, Family"
            required
        >
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Sức chứa tối đa <span class="text-danger">*</span></label>
        <input
            type="number"
            name="max_guests"
            min="1"
            class="form-control @error('max_guests') is-invalid @enderror"
            value="{{ old('max_guests', $roomType->max_guests ?? 1) }}"
            required
        >
        @error('max_guests')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Loại giường</label>
        <input
            type="text"
            name="bed_type"
            class="form-control"
            value="{{ old('bed_type', $roomType->bed_type ?? '') }}"
            placeholder="Ví dụ: 1 giường đôi"
        >
    </div>

    <div class="col-md-4">
        <label class="form-label">Diện tích m²</label>
        <input
            type="number"
            step="0.01"
            name="area"
            min="1"
            class="form-control @error('area') is-invalid @enderror"
            value="{{ old('area', $roomType->area ?? '') }}"
        >
        @error('area')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Giá một đêm <span class="text-danger">*</span></label>
        <input
            type="number"
            name="price_per_night"
            min="1"
            class="form-control @error('price_per_night') is-invalid @enderror"
            value="{{ old('price_per_night', $roomType->price_per_night ?? '') }}"
            placeholder="Ví dụ: 800000"
            required
        >
        @error('price_per_night')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Trạng thái <span class="text-danger">*</span></label>
        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
            <option value="active" @selected(old('status', $roomType->status ?? 'active') === 'active')>
                Đang bán
            </option>
            <option value="hidden" @selected(old('status', $roomType->status ?? '') === 'hidden')>
                Tạm ẩn
            </option>
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Tiện nghi hạng phòng</label>

        <div class="row g-2">
            @php
                $selectedAmenities = old('amenities', isset($roomType) ? $roomType->amenities->pluck('id')->toArray() : []);
            @endphp

            @forelse($amenities as $amenity)
                <div class="col-md-3">
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="amenities[]"
                            value="{{ $amenity->id }}"
                            id="amenity_{{ $amenity->id }}"
                            @checked(in_array($amenity->id, $selectedAmenities))
                        >
                        <label class="form-check-label" for="amenity_{{ $amenity->id }}">
                            {{ $amenity->name }}
                        </label>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <span class="text-muted small">Chưa có tiện nghi hạng phòng.</span>
                </div>
            @endforelse
        </div>
    </div>

    <div class="col-12">
        <label class="form-label">Mô tả</label>
        <textarea
            name="description"
            rows="4"
            class="form-control"
            placeholder="Mô tả hạng phòng"
        >{{ old('description', $roomType->description ?? '') }}</textarea>
    </div>

    <div class="col-md-6">
        <label class="form-label">Ảnh đại diện</label>
        <input
            type="file"
            name="thumbnail"
            class="form-control @error('thumbnail') is-invalid @enderror"
            accept="image/*"
        >
        @error('thumbnail')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror

        @if(!empty($roomType?->thumbnail))
            <div class="mt-3">
                <img
                    src="{{ asset('storage/' . $roomType->thumbnail) }}"
                    alt="Ảnh hạng phòng"
                    class="img-thumbnail"
                    style="max-height: 160px;"
                >
            </div>
        @endif
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary">
        Lưu thông tin
    </button>

    <a href="{{ route('owner.room-types.index') }}" class="btn btn-outline-secondary">
        Quay lại
    </a>
</div>