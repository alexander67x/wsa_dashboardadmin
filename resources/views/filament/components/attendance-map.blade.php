@php
    $hasCheckIn = $checkInLat !== null && $checkInLng !== null;
    $hasCheckOut = $checkOutLat !== null && $checkOutLng !== null;
    $mapId = 'attendance-map-' . ($recordId ?? uniqid());
@endphp

<x-dynamic-component :component="$field->getFieldWrapperView()" :field="$field">
    <div class="w-full">
        @if (! $hasCheckIn && ! $hasCheckOut)
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-500">
                Sin coordenadas registradas.
            </div>
        @else
            <div
                id="{{ $mapId }}"
                class="attendance-map h-[350px] w-full rounded-lg border border-gray-300"
                x-data="attendanceMap({
                    mapId: '{{ $mapId }}',
                    checkIn: {{ $hasCheckIn ? json_encode(['lat' => $checkInLat, 'lng' => $checkInLng, 'label' => $checkInLabel ?? 'Entrada']) : 'null' }},
                    checkOut: {{ $hasCheckOut ? json_encode(['lat' => $checkOutLat, 'lng' => $checkOutLng, 'label' => $checkOutLabel ?? 'Salida']) : 'null' }}
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
            .attendance-map {
                min-height: 300px;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            function attendanceMap(config) {
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

                        const markers = [];

                        if (config.checkIn) {
                            const checkInMarker = L.circleMarker([config.checkIn.lat, config.checkIn.lng], {
                                color: '#16a34a',
                                fillColor: '#22c55e',
                                fillOpacity: 0.9,
                                radius: 7,
                            }).addTo(map);
                            checkInMarker.bindPopup(config.checkIn.label || 'Entrada');
                            markers.push(checkInMarker);
                        }

                        if (config.checkOut) {
                            const checkOutMarker = L.circleMarker([config.checkOut.lat, config.checkOut.lng], {
                                color: '#dc2626',
                                fillColor: '#f87171',
                                fillOpacity: 0.9,
                                radius: 7,
                            }).addTo(map);
                            checkOutMarker.bindPopup(config.checkOut.label || 'Salida');
                            markers.push(checkOutMarker);
                        }

                        if (!markers.length) {
                            map.setView([0, 0], 2);
                            return;
                        }

                        if (markers.length === 1) {
                            map.setView(markers[0].getLatLng(), 16);
                            return;
                        }

                        const group = L.featureGroup(markers);
                        map.fitBounds(group.getBounds().pad(0.25));
                    }
                };
            }
        </script>
    @endpush
@endonce
