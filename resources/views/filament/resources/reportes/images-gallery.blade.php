<x-dynamic-component :component="$field->getFieldWrapperView()" :field="$field">
    @php
        $archivosConCoordenadas = $archivos->filter(fn($archivo) => $archivo->latitud && $archivo->longitud);
    @endphp

    <div class="space-y-6">
        <!-- Galería de imágenes -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($archivos as $archivo)
                <div class="relative group border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                    <a href="{{ $archivo->url }}" target="_blank" class="block">
                        <div class="aspect-video bg-gray-100 relative">
                            <img 
                                src="{{ $archivo->url }}" 
                                alt="{{ $archivo->nombre_original }}"
                                class="w-full h-full object-cover"
                                onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'400\' height=\'300\'%3E%3Crect fill=\'%23f3f4f6\' width=\'400\' height=\'300\'/%3E%3Ctext fill=\'%239ca3af\' font-family=\'sans-serif\' font-size=\'14\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\'%3EImagen no disponible%3C/text%3E%3C/svg%3E'"
                            >
                        </div>
                        
                    </a>
                </div>
            @endforeach
        </div>
        
        @if($archivos->isEmpty())
            <p class="text-sm text-gray-500 text-center py-4">No hay imágenes disponibles</p>
        @endif

        <!-- Mapa de ubicaciones -->
        @if($archivosConCoordenadas->isNotEmpty())
            @php
                $mapId = 'map-fotos-' . str_replace(['.', '[', ']'], ['-', '-', ''], $field->getStatePath());
                $archivosMapa = $archivosConCoordenadas->map(function($archivo) {
                    return [
                        'id' => $archivo->id_archivo,
                        'url' => $archivo->url,
                        'nombre' => $archivo->nombre_original,
                        'lat' => (float) $archivo->latitud,
                        'lng' => (float) $archivo->longitud,
                        'fecha' => $archivo->tomado_en ? $archivo->tomado_en->format('d/m/Y H:i') : null,
                    ];
                })->values()->all();
                
                // Calcular centro y zoom basado en todas las coordenadas
                $coordenadas = $archivosConCoordenadas->map(fn($a) => [(float)$a->latitud, (float)$a->longitud])->all();
                $centroLat = collect($coordenadas)->avg(fn($c) => $c[0]);
                $centroLng = collect($coordenadas)->avg(fn($c) => $c[1]);
                $centro = [$centroLat, $centroLng];
            @endphp
            <div class="mt-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Ubicaciones de las fotos</h3>
                <div 
                    x-data="fotosMapViewer({
                        mapId: '{{ $mapId }}',
                        archivos: {{ json_encode($archivosMapa) }},
                        center: {{ json_encode($centro) }},
                        zoom: 13
                    })"
                    x-init="initMap()"
                    class="w-full fotos-map-container"
                >
                    <div id="{{ $mapId }}" class="w-full h-[400px] rounded-lg border border-gray-300 shadow-lg"></div>
                </div>
            </div>
        @endif
    </div>
</x-dynamic-component>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .leaflet-container {
        height: 100% !important;
        width: 100% !important;
    }
    .fotos-map-container [id^="map-fotos-"] {
        z-index: 1;
        min-height: 400px;
        position: relative;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
function fotosMapViewer(config) {
    let map = null;
    let markers = [];
    let isInitialized = false;
    
    return {
        initMap() {
            if (isInitialized) {
                return;
            }
            
            setTimeout(() => {
                const mapContainer = document.getElementById(config.mapId);
                
                if (!mapContainer) return;
                
                if (typeof L === 'undefined') {
                    setTimeout(() => this.initMap(), 100);
                    return;
                }
                
                try {
                    // Calcular bounds de todas las coordenadas
                    const bounds = L.latLngBounds(
                        config.archivos.map(archivo => [archivo.lat, archivo.lng])
                    );
                    
                    // Inicializar mapa
                    map = L.map(config.mapId, {
                        center: config.center,
                        zoom: config.zoom
                    });
                    
                    // Agregar capa de OpenStreetMap (igual que map-picker)
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(map);
                    
                    // Ajustar el mapa para mostrar todos los marcadores
                    map.fitBounds(bounds, { padding: [50, 50] });
                    
                    // Agregar marcadores para cada foto
                    config.archivos.forEach((archivo) => {
                        const popupContent = `
                            <div class="p-2" style="min-width: 200px;">
                                <p class="font-semibold text-sm mb-1">${archivo.nombre}</p>
                                ${archivo.fecha ? `<p class="text-xs text-gray-600 mb-2">📅 ${archivo.fecha}</p>` : ''}
                                <p class="text-xs text-gray-500 mb-2">📍 ${archivo.lat.toFixed(6)}, ${archivo.lng.toFixed(6)}</p>
                                <a href="${archivo.url}" target="_blank" class="text-xs text-blue-600 hover:text-blue-800 underline">
                                    Ver imagen
                                </a>
                            </div>
                        `;
                        
                        const marker = L.marker([archivo.lat, archivo.lng], {
                            title: archivo.nombre
                        }).addTo(map);
                        
                        marker.bindPopup(popupContent);
                        markers.push(marker);
                    });
                    
                    // Ajustar tamaño del mapa después de renderizar
                    setTimeout(() => {
                        if (map) {
                            map.invalidateSize();
                        }
                    }, 300);
                    
                    isInitialized = true;
                    
                } catch (error) {
                    console.error('Error al inicializar el mapa de fotos:', error);
                }
            }, 200);
        }
    };
}
</script>
@endpush

