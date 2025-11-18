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
        
        <!-- Hidden inputs to sync with Filament -->
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
            console.log('initMap called', { isInitialized, map: !!map, marker: !!marker });
            
            if (isInitialized) {
                console.log('Map already initialized, skipping...');
                return;
            }
            
            setTimeout(() => {
                const mapContainer = document.getElementById('map-' + config.statePath);
                console.log('Map container found:', !!mapContainer);
                
                if (!mapContainer) return;
                
                try {
                    console.log('Initializing map...');
                    
                    // Initialize map
                    map = L.map('map-' + config.statePath, {
                        center: config.center,
                        zoom: config.zoom
                    });
                    
                    // Add tile layer
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(map);
                    
                    // Add marker
                    marker = L.marker([config.latitude, config.longitude], {
                        draggable: true
                    }).addTo(map);
                    
                    console.log('Map and marker created successfully');
                    
                    // Map click event
                    map.on('click', (e) => {
                        console.log('Map clicked:', e.latlng);
                        const lat = e.latlng.lat.toFixed(7);
                        const lng = e.latlng.lng.toFixed(7);
                        
                        this.latitude = lat;
                        this.longitude = lng;
                        
                        if (marker) {
                            marker.setLatLng([lat, lng]);
                        }
                        
                        this.updateHiddenInputs();
                    });
                    
                    // Marker drag event
                    marker.on('dragend', (e) => {
                        console.log('Marker dragged:', e.target.getLatLng());
                        const latlng = e.target.getLatLng();
                        this.latitude = latlng.lat.toFixed(7);
                        this.longitude = latlng.lng.toFixed(7);
                        this.updateHiddenInputs();
                    });
                    
                    // Resize map
                    setTimeout(() => {
                        if (map) {
                            map.invalidateSize();
                            console.log('Map resized');
                        }
                    }, 300);
                    
                    isInitialized = true;
                    console.log('Map initialization completed');
                    
                    // Add listeners for Filament form fields
                    this.addFormFieldListeners();
                    
                } catch (error) {
                    console.error('Map initialization error:', error);
                    isInitialized = false;
                }
            }, 200);
        },
        
        updateMap() {
            console.log('updateMap called', { map: !!map, marker: !!marker, lat: this.latitude, lng: this.longitude });
            
            if (!map || !marker) {
                console.log('Map or marker not available, reinitializing...');
                this.initMap();
                return;
            }
            
            const lat = parseFloat(this.latitude);
            const lng = parseFloat(this.longitude);
            
            if (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0) {
                marker.setLatLng([lat, lng]);
                map.setView([lat, lng], map.getZoom());
                // Update hidden inputs when map is updated from text fields
                this.updateHiddenInputs();
            }
        },
        
        updateHiddenInputs() {
            // Update hidden inputs for Filament
            const latInput = document.querySelector(`input[name="${config.statePath}[latitude]"]`);
            const lngInput = document.querySelector(`input[name="${config.statePath}[longitude]"]`);
            
            if (latInput) latInput.value = this.latitude;
            if (lngInput) lngInput.value = this.longitude;
            
            // Update hidden Filament form fields (Ubicaciones)
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
            
            // (Compat) Update proyecto form fields if they exist using the new column names
            // Prefer the unified `latitud` / `longitud` fields which are used by `proyectos`
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
            
            console.log('Updated form fields:', { lat: this.latitude, lng: this.longitude });
        },
        
        // Method to sync from Filament fields to map
        syncFromFormFields() {
            // Check for Ubicaciones form fields
            const latField = document.querySelector('input[name="latitud"]');
            const lngField = document.querySelector('input[name="longitud"]');
            
            let lat, lng;

            // Prefer `latitud`/`longitud` fields (unified in `proyectos`)
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
                
                console.log('Synced from form fields:', { lat, lng });
            }
        },
        
        // Add event listeners to Filament form fields
        addFormFieldListeners() {
            setTimeout(() => {
                // Listeners for Ubicaciones form
                const latField = document.querySelector('input[name="latitud"]');
                const lngField = document.querySelector('input[name="longitud"]');
                
                if (latField) {
                    latField.addEventListener('input', () => {
                        console.log('Latitude field changed:', latField.value);
                        this.syncFromFormFields();
                    });
                }

                if (lngField) {
                    lngField.addEventListener('input', () => {
                        console.log('Longitude field changed:', lngField.value);
                        this.syncFromFormFields();
                    });
                }
                
                console.log('Form field listeners added');
            }, 500);
        }
    }
}
</script>
