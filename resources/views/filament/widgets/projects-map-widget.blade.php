<x-filament-widgets::widget wire:ignore>
    <div class="w-full h-80 md:h-96 lg:h-[28rem]">
        <div id="projects-map" class="w-full h-full"></div>
    </div>
</x-filament-widgets::widget>

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        (function () {
            const container = document.getElementById('projects-map')
            if (! container || ! window.L) return

            const config = {
                center: [-16.2902, -63.5887],
                zoom: 5,
                projects: @js($projects),
            }

            const map = L.map(container).setView(config.center, config.zoom)

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(map)

            config.projects.forEach(project => {
                const estado = project.estado || 'activo'
                let color = 'red'

                if (estado === 'activo') color = 'green'
                if (estado === 'completado') color = 'blue'
                if (estado === 'pausado') color = 'orange'

                const marker = L.circleMarker([project.lat, project.lng], {
                    radius: 8,
                    color,
                    fillColor: color,
                    fillOpacity: 0.7,
                }).addTo(map)

                let popup = `<strong>${project.name}</strong>`
                if (project.client) {
                    popup += `<br><span style="font-size: 0.8rem;">${project.client}</span>`
                }
                popup += `<br><span style="font-size: 0.8rem;">Estado: ${estado}</span>`

                marker.bindPopup(popup)
            })
        })()
    </script>
@endpush

