@php
    $hotel = $hotel ?? null;
    $mapId = $mapId ?? 'hotel-map-' . ($hotel?->id ?? uniqid());
    $latValue = $lat ?? ($hotel->latitude ?? null);
    $lngValue = $lng ?? ($hotel->longitude ?? null);
    $hasCoordinates = is_numeric($latValue) && is_numeric($lngValue);
    $isDraggable = (bool) ($draggable ?? false);
    $fallbackLat = $fallbackLat ?? 21.028511;
    $fallbackLng = $fallbackLng ?? 105.804817;
    $title = $title ?? ($hotel->name ?? 'Travel Mate');
    $address = $address ?? trim(collect([
        $hotel->address ?? null,
        $hotel->ward ?? null,
        $hotel->district ?? null,
        $hotel->province ?? null,
    ])->filter()->implode(', '));
@endphp

@once
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @endpush
@endonce

@if($hasCoordinates || $isDraggable)
    <div class="tm-map-panel {{ $class ?? '' }}">
        @if(!empty($heading))
            <div class="tm-map-heading">
                <span><i class="bi bi-geo-alt"></i>{{ $heading }}</span>
                @if($hasCoordinates)
                    <small>{{ number_format((float) $latValue, 6) }}, {{ number_format((float) $lngValue, 6) }}</small>
                @endif
            </div>
        @endif

        <div
            id="{{ $mapId }}"
            class="tm-leaflet-map js-hotel-map"
            data-lat="{{ $hasCoordinates ? $latValue : '' }}"
            data-lng="{{ $hasCoordinates ? $lngValue : '' }}"
            data-fallback-lat="{{ $fallbackLat }}"
            data-fallback-lng="{{ $fallbackLng }}"
            data-title="{{ $title }}"
            data-address="{{ $address }}"
            data-draggable="{{ $isDraggable ? 'true' : 'false' }}"
            data-lat-input="{{ $latInputId ?? '' }}"
            data-lng-input="{{ $lngInputId ?? '' }}"
            data-address-input="{{ $addressInputId ?? '' }}"
            data-ward-input="{{ $wardInputId ?? '' }}"
            data-district-input="{{ $districtInputId ?? '' }}"
            data-province-input="{{ $provinceInputId ?? '' }}"
        ></div>

        @if($isDraggable)
            <div class="tm-map-help">
                <button type="button" class="tm-map-geocode-btn js-map-geocode" data-map-target="{{ $mapId }}">
                    <i class="bi bi-search"></i> Tim vi tri tu dia chi
                </button>
                <span>Bam tim vi tri, chon ket qua phu hop, sau do keo marker neu can chot dung diem.</span>
            </div>
            <div class="tm-map-results js-map-results" data-map-target="{{ $mapId }}" hidden></div>
        @elseif(!empty($address))
            <div class="tm-map-address">
                <i class="bi bi-pin-map"></i>{{ $address }}
            </div>
        @endif
    </div>
@elseif(!empty($address))
    <div class="tm-map-fallback {{ $class ?? '' }}">
        <i class="bi bi-geo-alt"></i>
        <div>
            <strong>Vi tri khach san</strong>
            <span>{{ $address }}</span>
        </div>
    </div>
@endif
