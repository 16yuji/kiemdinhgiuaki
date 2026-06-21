@csrf

<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label">Tên khách sạn <span class="text-danger">*</span></label>
        <input
            type="text"
            name="name"
            class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', $hotel->name ?? '') }}"
            placeholder="Ví dụ: Khách sạn Phenikaa Hà Nội"
            required
        >
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Trạng thái</label>
        <input
            type="text"
            class="form-control"
            value="{{ $hotel->status ?? 'active' }}"
            disabled
        >

        @if(!empty($hotel?->status_reason))
            <div class="form-text text-danger">
                Lý do xử lý: {{ $hotel->status_reason }}
            </div>
        @else
            <div class="form-text">
                Admin có quyền ẩn/khóa khách sạn nếu vi phạm.
            </div>
        @endif
    </div>

    <div class="col-md-4">
        <label class="form-label">Tỉnh/Thành phố</label>
        <input
            type="text"
            name="province"
            id="hotel_province"
            class="form-control"
            value="{{ old('province', $hotel->province ?? '') }}"
            placeholder="Ví dụ: Hà Nội"
        >
    </div>

    <div class="col-md-4">
        <label class="form-label">Quận/Huyện</label>
        <input
            type="text"
            name="district"
            id="hotel_district"
            class="form-control"
            value="{{ old('district', $hotel->district ?? '') }}"
            placeholder="Ví dụ: Hà Đông"
        >
    </div>

    <div class="col-md-4">
        <label class="form-label">Phường/Xã</label>
        <input
            type="text"
            name="ward"
            id="hotel_ward"
            class="form-control"
            value="{{ old('ward', $hotel->ward ?? '') }}"
            placeholder="Ví dụ: Yên Nghĩa"
        >
    </div>

    <div class="col-12">
        <label class="form-label">Địa chỉ chi tiết <span class="text-danger">*</span></label>
        <input
            type="text"
            name="address"
            id="hotel_address"
            class="form-control @error('address') is-invalid @enderror"
            value="{{ old('address', $hotel->address ?? '') }}"
            placeholder="Nhập địa chỉ chi tiết"
            required
        >
        @error('address')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Latitude</label>
        <input
            type="number"
            step="0.0000001"
            min="-90"
            max="90"
            name="latitude"
            id="hotel_latitude"
            class="form-control @error('latitude') is-invalid @enderror"
            value="{{ old('latitude', $hotel->latitude ?? '') }}"
            placeholder="Vi du: 21.028511"
        >
        @error('latitude')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Longitude</label>
        <input
            type="number"
            step="0.0000001"
            min="-180"
            max="180"
            name="longitude"
            id="hotel_longitude"
            class="form-control @error('longitude') is-invalid @enderror"
            value="{{ old('longitude', $hotel->longitude ?? '') }}"
            placeholder="Vi du: 105.804817"
        >
        @error('longitude')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        @include('shared.hotel-map', [
            'hotel' => $hotel,
            'mapId' => 'owner-hotel-location-map',
            'heading' => 'Vi tri ban do',
            'draggable' => true,
            'latInputId' => 'hotel_latitude',
            'lngInputId' => 'hotel_longitude',
            'addressInputId' => 'hotel_address',
            'wardInputId' => 'hotel_ward',
            'districtInputId' => 'hotel_district',
            'provinceInputId' => 'hotel_province',
            'class' => 'tm-map-panel-form',
        ])
    </div>

    <div class="col-md-6">
        <label class="form-label">Giờ nhận phòng</label>
        <input
            type="time"
            name="checkin_time"
            class="form-control"
            value="{{ old('checkin_time', isset($hotel) && $hotel->checkin_time ? \Carbon\Carbon::parse($hotel->checkin_time)->format('H:i') : '14:00') }}"
        >
    </div>

    <div class="col-md-6">
        <label class="form-label">Giờ trả phòng</label>
        <input
            type="time"
            name="checkout_time"
            class="form-control"
            value="{{ old('checkout_time', isset($hotel) && $hotel->checkout_time ? \Carbon\Carbon::parse($hotel->checkout_time)->format('H:i') : '12:00') }}"
        >
    </div>

    <div class="col-12">
        <label class="form-label">Tiện nghi chung</label>

        <div class="row g-2">
            @forelse($amenities as $amenity)
                <div class="col-md-4">
                    <label class="border rounded p-2 d-block">
                        <input
                            type="checkbox"
                            name="amenity_ids[]"
                            value="{{ $amenity->id }}"
                            class="form-check-input me-2"
                            @checked(in_array($amenity->id, old('amenity_ids', isset($hotel) ? $hotel->amenities->pluck('id')->toArray() : [])))
                        >
                        {{ $amenity->name }}
                    </label>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-warning mb-0">
                        Chưa có dữ liệu tiện nghi. Hãy kiểm tra AmenitySeeder.
                    </div>
                </div>
            @endforelse
        </div>

        @error('amenity_ids')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Mô tả</label>
        <textarea
            name="description"
            rows="4"
            class="form-control"
            placeholder="Mô tả ngắn về khách sạn"
        >{{ old('description', $hotel->description ?? '') }}</textarea>
    </div>

    <div class="col-12">
        <label class="form-label">Chính sách hủy / hoàn tiền</label>
        <textarea
            name="cancellation_policy"
            rows="5"
            class="form-control @error('cancellation_policy') is-invalid @enderror"
            placeholder="Ví dụ: Khách được hủy miễn phí trước ngày nhận phòng 24 giờ. Hủy trong ngày nhận phòng có thể không được hoàn tiền..."
        >{{ old('cancellation_policy', $hotel->cancellation_policy ?? '') }}</textarea>

        @error('cancellation_policy')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror

        <div class="form-text">
            Chính sách này sẽ hiển thị cho khách hàng và Admin dùng để xử lý hoàn tiền thủ công.
        </div>
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

        @if(!empty($hotel?->thumbnail))
            <div class="mt-3">
                <img
                    src="{{ asset('storage/' . $hotel->thumbnail) }}"
                    alt="Ảnh khách sạn"
                    class="img-thumbnail"
                    style="max-height: 160px;"
                >
            </div>
        @endif
    </div>

    <div class="col-md-6">
        <label class="form-label">Thư viện ảnh khách sạn</label>
        <input
            type="file"
            name="gallery_images[]"
            class="form-control @error('gallery_images.*') is-invalid @enderror"
            accept="image/*"
            multiple
        >
        <div class="form-text">
            Có thể chọn nhiều ảnh. Mỗi ảnh tối đa 4MB.
        </div>
        @error('gallery_images.*')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary">
        Lưu thông tin
    </button>

    <a href="{{ route('owner.hotels.index') }}" class="btn btn-outline-secondary">
        Quay lại
    </a>
</div>
