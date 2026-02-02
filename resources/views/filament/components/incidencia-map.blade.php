@php
    $hasCoords = $lat !== null && $lng !== null;
    $mapId = 'incidencia-map-' . ($recordId ?? uniqid());
@endphp

<x-dynamic-component :component="$field->getFieldWrapperView()" :field="$field">
    <div class="w-full">
        @if (! $hasCoords)
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-500">
                Sin coordenadas registradas.
            </div>
        @else
            <div
                id="{{ $mapId }}"
                class="incidencia-map h-[350px] w-full rounded-lg border border-gray-300"
                x-data="incidenciaMap({
                    mapId: '{{ $mapId }}',
                    point: {{ json_encode(['lat' => $lat, 'lng' => $lng, 'label' => $label ?? 'Incidencia']) }}
                })"
                x-init="init()"
            ></div>
        @endif
    </div>
</x-dynamic-component>

@once
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <style>
            .incidencia-map {
                min-height: 300px;
                position: relative;
                z-index: 0;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            function incidenciaMap(config) {
                return {
                    init() {
                        const mapEl = document.getElementById(config.mapId);
                        if (!mapEl || !window.L) {
                            return;
                        }

                        const map = L.map(config.mapId, {
                            zoomControl: true,
                        });

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '© OpenStreetMap contributors'
                        }).addTo(map);

                        const marker = L.circleMarker([config.point.lat, config.point.lng], {
                            color: '#2563eb',
                            fillColor: '#3b82f6',
                            fillOpacity: 0.9,
                            radius: 7,
                        }).addTo(map);
                        marker.bindPopup(config.point.label || 'Incidencia');

                        map.setView(marker.getLatLng(), 16);
                    }
                };
            }
        </script>
    @endpush
@endonce
