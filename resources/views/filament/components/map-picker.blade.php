<x-dynamic-component :component="$field->getFieldWrapperView()" :field="$field">
    <div 
        x-data="mapPicker({
            statePath: '{{ $field->getStatePath() }}',
            latitude: {{ $latitude }},
            longitude: {{ $longitude }},
            zoom: {{ $zoom }},
            center: {{ json_encode($center) }}
        })"
        x-init="initMap()"
        class="w-full map-picker-container"
    >
        <div id="map-{{ $field->getStatePath() }}" class="w-full h-[500px] md:h-[600px] lg:h-[700px] xl:h-[800px] rounded-lg border border-gray-300 shadow-lg"></div>
        
        <!-- Inputs ocultos para sincronizar con Filament -->
        <input type="hidden" x-model="latitude" name="{{ $field->getStatePath() }}[latitude]" />
        <input type="hidden" x-model="longitude" name="{{ $field->getStatePath() }}[longitude]" />
    </div>
</x-dynamic-component>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .leaflet-container {
        height: 100% !important;
        width: 100% !important;
        min-height: 400px;
    }
    #map-{{ $field->getStatePath() }} {
        z-index: 1;
        min-height: 400px;
        position: relative;
    }
    
    /* Asegurar que el contenedor padre no limite el tamaño */
    .map-picker-container {
        width: 100%;
        max-width: 100%;
    }
    
    /* Mejorar la responsividad */
    @media (max-width: 768px) {
        #map-{{ $field->getStatePath() }} {
            height: 400px !important;
        }
    }
    
    @media (min-width: 769px) and (max-width: 1024px) {
        #map-{{ $field->getStatePath() }} {
            height: 500px !important;
        }
    }
    
    @media (min-width: 1025px) {
        #map-{{ $field->getStatePath() }} {
            height: 600px !important;
        }
    }
    
    @media (min-width: 1280px) {
        #map-{{ $field->getStatePath() }} {
            height: 700px !important;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endpush

<script>
function mapPicker(config) {
    let map = null;
    let marker = null;
    let isInitialized = false;
    
    return {
        latitude: config.latitude,
        longitude: config.longitude,
        zoom: config.zoom,
        center: config.center,
        
        initMap() {
            console.log('initMap llamado', { isInitialized, map: !!map, marker: !!marker });
            
            if (isInitialized) {
                console.log('Mapa ya inicializado, se omite...');
                return;
            }
            
            setTimeout(() => {
                const mapContainer = document.getElementById('map-' + config.statePath);
                console.log('Contenedor del mapa encontrado:', !!mapContainer);
                
                if (!mapContainer) return;
                
                try {
                    console.log('Inicializando mapa...');
                    
                    // Inicializar mapa
                    map = L.map('map-' + config.statePath, {
                        center: config.center,
                        zoom: config.zoom
                    });
                    
                    // Agregar capa de teselas
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(map);
                    
                    // Agregar marcador
                    marker = L.marker([config.latitude, config.longitude], {
                        draggable: true
                    }).addTo(map);
                    
                    console.log('Mapa y marcador creados correctamente');
                    
                    // Evento de clic en el mapa
                    map.on('click', (e) => {
                        console.log('Clic en el mapa:', e.latlng);
                        const lat = e.latlng.lat.toFixed(7);
                        const lng = e.latlng.lng.toFixed(7);
                        
                        this.latitude = lat;
                        this.longitude = lng;
                        
                        if (marker) {
                            marker.setLatLng([lat, lng]);
                        }
                        
                        this.updateHiddenInputs();
                    });
                    
                    // Evento de arrastre del marcador
                    marker.on('dragend', (e) => {
                        console.log('Marcador arrastrado:', e.target.getLatLng());
                        const latlng = e.target.getLatLng();
                        this.latitude = latlng.lat.toFixed(7);
                        this.longitude = latlng.lng.toFixed(7);
                        this.updateHiddenInputs();
                    });
                    
                    // Redimensionar mapa
                    setTimeout(() => {
                        if (map) {
                            map.invalidateSize();
                            console.log('Mapa redimensionado');
                        }
                    }, 300);
                    
                    isInitialized = true;
                    console.log('Inicializacion del mapa completada');
                    
                    // Agregar escuchas para campos del formulario de Filament
                    this.addFormFieldListeners();
                    
                } catch (error) {
                    console.error('Error al inicializar el mapa:', error);
                    isInitialized = false;
                }
            }, 200);
        },
        
        updateMap() {
            console.log('updateMap llamado', { map: !!map, marker: !!marker, lat: this.latitude, lng: this.longitude });
            
            if (!map || !marker) {
                console.log('Mapa o marcador no disponible, reinicializando...');
                this.initMap();
                return;
            }
            
            const lat = parseFloat(this.latitude);
            const lng = parseFloat(this.longitude);
            
            if (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0) {
                marker.setLatLng([lat, lng]);
                map.setView([lat, lng], map.getZoom());
                // Actualizar inputs ocultos cuando el mapa se actualiza desde los campos de texto
                this.updateHiddenInputs();
            }
        },
        
        updateHiddenInputs() {
            // Actualizar inputs ocultos para Filament
            const latInput = document.querySelector(`input[name="${config.statePath}[latitude]"]`);
            const lngInput = document.querySelector(`input[name="${config.statePath}[longitude]"]`);
            
            if (latInput) latInput.value = this.latitude;
            if (lngInput) lngInput.value = this.longitude;
            
            // Actualizar campos ocultos del formulario de Filament (Ubicaciones)
            const latField = document.querySelector('input[name="latitud"]');
            const lngField = document.querySelector('input[name="longitud"]');
            
            if (latField) {
                latField.value = this.latitude;
                latField.dispatchEvent(new Event('input', { bubbles: true }));
            }
            
            if (lngField) {
                lngField.value = this.longitude;
                lngField.dispatchEvent(new Event('input', { bubbles: true }));
            }
            
            // (Compat) Actualizar campos del formulario de proyecto si existen usando los nuevos nombres de columnas
            // Preferir los campos unificados `latitud` / `longitud` que usa `proyectos`
            const latProyectoField = document.querySelector('input[name="latitud"]');
            const lngProyectoField = document.querySelector('input[name="longitud"]');

            if (latProyectoField) {
                latProyectoField.value = this.latitude;
                latProyectoField.dispatchEvent(new Event('input', { bubbles: true }));
            }

            if (lngProyectoField) {
                lngProyectoField.value = this.longitude;
                lngProyectoField.dispatchEvent(new Event('input', { bubbles: true }));
            }
            
            console.log('Campos del formulario actualizados:', { lat: this.latitude, lng: this.longitude });
        },
        
        // Metodo para sincronizar desde campos de Filament al mapa
        syncFromFormFields() {
            // Verificar campos del formulario de Ubicaciones
            const latField = document.querySelector('input[name="latitud"]');
            const lngField = document.querySelector('input[name="longitud"]');
            
            let lat, lng;

            // Preferir campos `latitud`/`longitud` (unificados en `proyectos`)
            if (latField && lngField) {
                lat = parseFloat(latField.value);
                lng = parseFloat(lngField.value);
            }
            
            if (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0) {
                this.latitude = lat;
                this.longitude = lng;
                
                if (map && marker) {
                    marker.setLatLng([lat, lng]);
                    map.setView([lat, lng], map.getZoom());
                }
                
                console.log('Sincronizado desde campos del formulario:', { lat, lng });
            }
        },
        
        // Agregar escuchas a los campos del formulario de Filament
        addFormFieldListeners() {
            setTimeout(() => {
                // Escuchas para formulario de Ubicaciones
                const latField = document.querySelector('input[name="latitud"]');
                const lngField = document.querySelector('input[name="longitud"]');
                
                if (latField) {
                    latField.addEventListener('input', () => {
                        console.log('Campo de latitud cambiado:', latField.value);
                        this.syncFromFormFields();
                    });
                }

                if (lngField) {
                    lngField.addEventListener('input', () => {
                        console.log('Campo de longitud cambiado:', lngField.value);
                        this.syncFromFormFields();
                    });
                }
                
                console.log('Escuchas de campos agregadas');
            }, 500);
        }
    }
}
</script>
