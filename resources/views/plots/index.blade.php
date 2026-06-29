<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Burial Plotting & Blocks</h1>
                <a href="{{ route('plots.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm">+ Add Plot</a>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div style="display: flex; gap: 20px;">
                        <div style="width: 320px; flex-shrink: 0;">
                            <input type="text" id="search-input" placeholder="Search plot number or section…" style="width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px;">
                            <div class="mb-3 flex gap-2 text-xs">
                                <span class="px-2 py-1 rounded-full bg-green-100 text-green-800">Available</span>
                                <span class="px-2 py-1 rounded-full bg-yellow-100 text-yellow-800">Reserved</span>
                                <span class="px-2 py-1 rounded-full bg-red-100 text-red-800">Occupied</span>
                                <span class="px-2 py-1 rounded-full bg-red-200 text-red-900">Full</span>
                            </div>
                            <div id="plot-list" style="max-height: 500px; overflow-y: auto; border: 1px solid #e5e7eb; border-radius: 4px; padding: 8px;">
                                @forelse($plots as $plot)
                                    <div class="plot-entry" data-id="{{ $plot->id }}" style="padding: 8px; border-bottom: 1px solid #e5e7eb; cursor: pointer;">
                                        <div class="flex items-center justify-between">
                                            <strong>{{ $plot->plot_number }}</strong>
                                            <span class="badge" style="padding: 2px 8px; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;
                                                @if($plot->status === 'occupied') background: #fee2e2; color: #991b1b;
                                                @elseif($plot->status === 'reserved') background: #fef3c7; color: #92400e;
                                                @elseif($plot->status === 'full') background: #fecaca; color: #7f1d1d;
                                                @else background: #d1fae5; color: #065f46;
                                                @endif
                                            ">{{ $plot->status }}</span>
                                        </div>
                                        <span style="display: block; font-size: 0.875rem; color: #6b7280;">
                                            {{ $plot->section ?? 'No section' }}
                                            @if($plot->lot_type) · {{ ucfirst($plot->lot_type) }} @endif
                                            @if($plot->dimension) · {{ $plot->dimension }} @endif
                                        </span>
                                        <span style="display: block; font-size: 0.75rem; color: #9ca3af;">
                                            {{ $plot->burials_count }}/{{ $plot->capacity }} burials
                                        </span>
                                    </div>
                                @empty
                                    <p class="text-center text-gray-500 py-8 text-sm">No plots yet. Click "+ Add Plot" to start.</p>
                                @endforelse
                            </div>
                        </div>
                        <div id="map" style="flex: 1; height: 600px; border: 2px solid #e5e7eb; border-radius: 4px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
    <script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>
    <script>
        const PLOTS = @json($plotData);
        const BOUNDARY = @json($boundary);
        const UPDATE_URL = "{{ route('plots.position', ':id') }}";
        const EDIT_URL = "{{ route('plots.edit', ':id') }}";
        const CSRF = "{{ csrf_token() }}";

        const map = L.map('map', {
            center: [16.5253, 121.1906],
            zoom: 19,
            minZoom: 17,
            maxZoom: 21,
            maxBounds: L.latLngBounds([16.5217, 121.1862], [16.5290, 121.1951]),
            maxBoundsViscosity: 1.0,
        });

        const satellite = L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            attribution: '&copy; Google',
        });

        const osm = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a>',
        });

        const customTiles = L.tileLayer('/tiles/{z}/{x}/{y}.png', {
            minZoom: 20,
            maxZoom: 21,
            maxNativeZoom: 20,
            errorTileUrl: '/tiles/placeholder.png',
        }).setZIndex(10);

        satellite.addTo(map);
        customTiles.addTo(map);

        L.control.layers({
            'Satellite': satellite,
            'OpenStreetMap': osm,
        }, {
            'Cemetery tiles': customTiles,
        }, { position: 'topleft' }).addTo(map);

        if (BOUNDARY) {
            const geom = BOUNDARY.geometry || BOUNDARY;
            L.geoJSON(geom, {
                style: { color: '#059669', weight: 3, fillColor: '#059669', fillOpacity: 0.08 },
            }).addTo(map);
        }

        const drawnItems = new L.FeatureGroup();
        map.addLayer(drawnItems);

        const drawControl = new L.Control.Draw({
            position: 'topright',
            draw: {
                polygon: { shapeOptions: { color: '#6366f1', weight: 3, fillOpacity: 0.35 } },
                rectangle: { shapeOptions: { color: '#6366f1', weight: 3, fillOpacity: 0.35 } },
                circle: false,
                circlemarker: false,
                marker: false,
                polyline: false,
            },
            edit: {
                featureGroup: drawnItems,
                edit: true,
                remove: false,
            },
        });

        function statusStyle(status) {
            switch (status) {
                case 'reserved': return { color: '#d97706', fillColor: '#f59e0b', weight: 4, fillOpacity: 0.25 };
                case 'occupied':
                case 'full':     return { color: '#dc2626', fillColor: '#ef4444', weight: 4, fillOpacity: 0.25 };
                default:         return { color: '#16a34a', fillColor: '#22c55e', weight: 4, fillOpacity: 0.25 };
            }
        }

        function addPlotShape(plot) {
            const shape = plot.shape?.geometry || plot.shape;
            if (shape && shape.coordinates) {
                const geojson = shape.type ? shape : { type: 'Feature', geometry: shape, properties: {} };
                const layer = L.geoJSON(geojson, {
                    style: statusStyle(plot.status),
                }).addTo(drawnItems);
                layer._plotId = plot.id;
                layer.bindPopup(`
                    <b>${plot.plot_number}</b><br>
                    ${plot.section ? plot.section + '<br>' : ''}
                    ${plot.lot_type ? ucfirst(plot.lot_type) + ' lot' + (plot.dimension ? ' · ' + plot.dimension : '') + '<br>' : ''}
                    Status: <span style="color:${plot.status === 'available' ? '#22c55e' : plot.status === 'reserved' ? '#f59e0b' : '#ef4444'}">${plot.status}</span><br>
                    Burials: ${plot.burials_count}/${plot.capacity}
                    ${plot.price ? '<br>Price: ₱' + Number(plot.price).toLocaleString() : ''}
                    <br><a href="${EDIT_URL.replace(':id', plot.id || plot._plotId)}" style="color:#4f46e5;font-size:0.875rem;">Edit</a>
                `);
            } else if (plot.lat && plot.lng) {
                const color = plot.status === 'occupied' ? '#ef4444'
                    : plot.status === 'reserved' ? '#f59e0b'
                    : plot.status === 'full' ? '#dc2626' : '#22c55e';
                const marker = L.circleMarker([plot.lat, plot.lng], {
                    radius: 8, color, fillColor: color, fillOpacity: 0.6, weight: 2,
                }).addTo(drawnItems);
                marker._plotId = plot.id;
                marker.bindPopup(`
                    <b>${plot.plot_number}</b><br>
                    ${plot.section ?? ''}<br>
                    Status: ${plot.status}<br>
                    Burials: ${plot.burials_count}/${plot.capacity}
                    <br><a href="${EDIT_URL.replace(':id', plot.id || plot._plotId)}" style="color:#4f46e5;font-size:0.875rem;">Edit</a>
                `);
                map.addLayer(marker);
            }
        }

        function ucfirst(s) { return s.charAt(0).toUpperCase() + s.slice(1); }

        PLOTS.forEach(addPlotShape);

        const drawnLayerRef = { layer: null };

        function isInsideBoundary(geoJson) {
            if (!BOUNDARY) return true;
            const boundaryGeom = BOUNDARY.geometry || BOUNDARY;
            const boundaryPoly = turf.polygon(boundaryGeom.coordinates);
            const coords = geoJson.geometry?.coordinates || geoJson.coordinates;
            if (!coords) return false;
            const ring = coords[0];
            return ring.every(pt => {
                const point = turf.point(pt);
                return turf.booleanPointInPolygon(point, boundaryPoly);
            });
        }

        map.on(L.Draw.Event.CREATED, function(event) {
            const layer = event.layer;
            const geoJson = layer.toGeoJSON();
            const bounds = layer.getBounds();
            const center = bounds.getCenter();

            if (!isInsideBoundary(geoJson)) {
                drawnItems.addLayer(layer);
                layer.setStyle({ color: '#dc2626', weight: 3, fillOpacity: 0.2 });
                document.getElementById('draw-form-error').classList.remove('hidden');
                document.getElementById('draw-form-lat').value = '';
                document.getElementById('draw-form-lng').value = '';
                document.getElementById('draw-form-shape').value = '';
                return;
            }

            document.getElementById('draw-form-error').classList.add('hidden');
            drawnLayerRef.layer = layer;
            document.getElementById('draw-form-lat').value = center.lat.toFixed(6);
            document.getElementById('draw-form-lng').value = center.lng.toFixed(6);
            document.getElementById('draw-form-shape').value = JSON.stringify(geoJson);
            document.getElementById('draw-form-modal').classList.remove('hidden');
            document.getElementById('draw-form-plot_number').focus();

            drawnItems.addLayer(layer);
            map.setView(center, 20, { animate: true });
        });

        function cancelDrawForm() {
            document.getElementById('draw-form-modal').classList.add('hidden');
            if (drawnLayerRef.layer) {
                drawnItems.removeLayer(drawnLayerRef.layer);
                drawnLayerRef.layer = null;
            }
        }

        document.getElementById('draw-form-cancel').addEventListener('click', cancelDrawForm);

        document.getElementById('draw-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = e.target;
            const data = {
                plot_number: form.plot_number.value,
                section: form.section.value,
                lat: form.lat.value,
                lng: form.lng.value,
                shape: form.shape.value,
                lot_type: form.lot_type.value,
                dimension: form.dimension.value,
                capacity: form.capacity.value,
                price: form.price.value,
                status: form.status.value,
                notes: form.notes.value,
                _token: CSRF,
            };
            fetch('{{ route('plots.store') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify(data),
            })
            .then(r => r.json())
            .then(res => {
                const plot = {
                    id: res.id, plot_number: data.plot_number, section: data.section,
                    lat: parseFloat(data.lat), lng: parseFloat(data.lng),
                    shape: JSON.parse(data.shape), lot_type: data.lot_type,
                    dimension: data.dimension, capacity: parseInt(data.capacity),
                    burials_count: 0, current_occupants: 0, price: parseFloat(data.price),
                    status: data.status,
                };
                addPlotShape(plot);
                PLOTS.push(plot);
                cancelDrawForm();
                location.reload();
            })
            .catch(() => alert('Failed to save plot'));
        });

        document.querySelectorAll('.plot-entry').forEach(el => {
            el.addEventListener('click', function() {
                const id = parseInt(this.dataset.id);
                const plot = PLOTS.find(p => p.id === id);
                if (!plot) return;
                const shape = plot.shape?.geometry || plot.shape;
                if (shape && shape.coordinates) {
                    const layer = drawnItems.getLayers().find(l => l._plotId === id && l.getBounds);
                    if (layer) {
                        map.flyTo(layer.getBounds().getCenter(), 19, { duration: 0.5 });
                        layer.openPopup();
                    }
                } else if (plot.lat && plot.lng) {
                    map.flyTo([plot.lat, plot.lng], 19, { duration: 0.5 });
                }
            });
        });

        map.on(L.Draw.Event.EDITED, function(event) {
            const layers = event.layers;
            layers.eachLayer(function(layer) {
                const geoJson = layer.toGeoJSON();
                const bounds = layer.getBounds();
                const center = bounds.getCenter();
                const existingPopup = layer.getPopup();
                if (existingPopup) {
                    const plot = PLOTS.find(p => p.id === layer._plotId);
                    if (plot) {
                        plot.lat = center.lat;
                        plot.lng = center.lng;
                        if (plot.shape) {
                            plot.shape = layer.toGeoJSON().geometry || layer.toGeoJSON();
                        }
                    }
                }
            });
        });

        document.getElementById('search-input').addEventListener('input', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.plot-entry').forEach(el => {
                el.style.display = el.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });

        map.on('draw:drawstart', function() {
            if (document.getElementById('draw-form-modal') && !document.getElementById('draw-form-modal').classList.contains('hidden')) {
                cancelDrawForm();
            }
        });
    </script>

    <div id="draw-form-modal" class="hidden fixed inset-0 bg-black/40 z-[2000] flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-bold text-gray-900 mb-4">New Plot</h3>
            <div id="draw-form-error" class="hidden mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 flex items-center gap-2">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                <span>Plot must be <strong>inside</strong> the cemetery boundary.</span>
            </div>
            <form id="draw-form">
                <input type="hidden" name="lat" id="draw-form-lat">
                <input type="hidden" name="lng" id="draw-form-lng">
                <input type="hidden" name="shape" id="draw-form-shape">

                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Plot Number</label>
                        <input type="text" name="plot_number" id="draw-form-plot_number" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Section / Block</label>
                        <input type="text" name="section" placeholder="e.g. Block A" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Lot Type</label>
                            <select name="lot_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="individual">Individual</option>
                                <option value="family">Family</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Dimension</label>
                            <input type="text" name="dimension" placeholder="e.g. 1.5m × 2.5m" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Capacity</label>
                            <input type="number" name="capacity" value="1" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Price (₱)</label>
                            <input type="number" step="0.01" name="price" value="0" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="available">Available</option>
                            <option value="reserved">Reserved</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <button type="button" id="draw-form-cancel" class="text-gray-600 hover:text-gray-900 text-sm">Cancel</button>
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm">Save Plot</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>