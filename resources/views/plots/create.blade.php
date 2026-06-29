<x-app-layout>
    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Add Plot</h1>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="grid lg:grid-cols-5 gap-8">
                    <div class="lg:col-span-3">
                        <p class="text-sm font-medium text-gray-700 mb-2">Draw a rectangle on the map inside the cemetery boundary</p>
                        <div id="map" style="height: 500px; border: 2px solid #e5e7eb; border-radius: 6px;"></div>
                        <div id="boundary-alert" class="hidden mt-2 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 flex items-center gap-2">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            <span>Plot must be drawn <strong>inside</strong> the cemetery boundary. Please reposition.</span>
                        </div>
                        <button id="confirm-shape-btn" class="hidden mt-2 w-full px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-md hover:bg-emerald-700">Confirm Shape</button>
                        <p class="text-xs text-gray-500 mt-2">Draw a rectangle, then use the handles to rotate and scale. Click <strong>Confirm Shape</strong> to finalize before saving.</p>
                    </div>
                    <div class="lg:col-span-2">
                        <form method="POST" action="{{ route('plots.store') }}">
                            @csrf
                            <input type="hidden" name="lat" id="lat-input" value="{{ old('lat') }}">
                            <input type="hidden" name="lng" id="lng-input" value="{{ old('lng') }}">
                            <input type="hidden" name="shape" id="shape-input" value="{{ old('shape') }}">

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Plot Number</label>
                                    <input type="text" name="plot_number" value="{{ old('plot_number') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Section / Block</label>
                                    <input type="text" name="section" value="{{ old('section') }}" placeholder="e.g. Block A" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Lot Type</label>
                                        <select name="lot_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="individual">Individual</option>
                                            <option value="family">Family</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Dimension</label>
                                        <input type="text" name="dimension" value="{{ old('dimension') }}" placeholder="e.g. 1.5m × 2.5m" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Capacity</label>
                                        <input type="number" name="capacity" value="{{ old('capacity', 1) }}" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Price (₱)</label>
                                        <input type="number" step="0.01" name="price" value="{{ old('price', 0) }}" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Status</label>
                                    <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="available">Available</option>
                                        <option value="reserved">Reserved</option>
                                    </select>
                                </div>
                                <div class="grid grid-cols-2 gap-4 p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500">Centroid Latitude</label>
                                        <p id="lat-display" class="mt-0.5 text-sm font-mono text-gray-900">—</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500">Centroid Longitude</label>
                                        <p id="lng-display" class="mt-0.5 text-sm font-mono text-gray-900">—</p>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Notes</label>
                                    <textarea name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                                </div>
                                <div class="flex items-center justify-end gap-4 pt-2">
                                    <a href="{{ route('plots.index') }}" class="text-gray-600 hover:text-gray-900">Cancel</a>
                                    <button type="submit" id="submit-btn" disabled class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 opacity-50 cursor-not-allowed">Save</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/leaflet-path-transform@1.9.0/dist/index.js"></script>
    <script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>
    <script>
        const BOUNDARY = @json($boundary);
        const map = L.map('map', {
            center: [16.5253, 121.1906],
            zoom: 19,
            minZoom: 17,
            maxZoom: 20,
            maxBounds: L.latLngBounds([16.5217, 121.1862], [16.5290, 121.1951]),
            maxBoundsViscosity: 1.0,
        });

        L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            attribution: '&copy; Google',
        }).addTo(map);

        if (BOUNDARY) {
            const geom = BOUNDARY.geometry || BOUNDARY;
            const boundaryLayer = L.geoJSON(geom, {
                style: { color: '#059669', weight: 3, fillColor: '#059669', fillOpacity: 0.08 },
            }).addTo(map);
            map.fitBounds(boundaryLayer.getBounds().pad(0.1));
        }

        const drawnItems = new L.FeatureGroup();
        map.addLayer(drawnItems);

        const drawControl = new L.Control.Draw({
            position: 'topright',
            draw: {
                rectangle: { shapeOptions: { color: '#6366f1', weight: 3, fillOpacity: 0.35 } },
                polygon:   { shapeOptions: { color: '#6366f1', weight: 3, fillOpacity: 0.35 } },
                circle: false, circlemarker: false, marker: false, polyline: false,
            },
            edit: {
                featureGroup: drawnItems,
                edit: true,
                remove: false,
            },
        });
        map.addControl(drawControl);

        const alertEl = document.getElementById('boundary-alert');
        const submitBtn = document.getElementById('submit-btn');
        const confirmBtn = document.getElementById('confirm-shape-btn');
        let currentLayer = null;
        let centroidMarker = null;

        function updateCentroidMarker(latlng) {
            if (centroidMarker) map.removeLayer(centroidMarker);
            centroidMarker = L.circleMarker(latlng, {
                radius: 5, color: '#059669', fillColor: '#059669',
                fillOpacity: 1, weight: 2,
            }).addTo(map);
        }

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

        function readShapeFromLayer(layer) {
            const latlngs = layer.getLatLngs();
            const ring = Array.isArray(latlngs[0]) ? latlngs[0] : latlngs;
            const coords = ring.map(ll => [ll.lng, ll.lat]);
            coords.push(coords[0]);
            return {
                type: 'Feature',
                geometry: { type: 'Polygon', coordinates: [coords] },
                properties: {},
            };
        }

        function confirmShape() {
            if (!currentLayer) return;
            const geojson = readShapeFromLayer(currentLayer);

            if (!isInsideBoundary(geojson)) {
                alertEl.classList.remove('hidden');
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                return;
            }
            alertEl.classList.add('hidden');

            document.getElementById('shape-input').value = JSON.stringify(geojson);

            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }

        function toPolygon(layer) {
            if (layer instanceof L.Rectangle) {
                const latlngs = layer.getLatLngs();
                const opts = layer.options;
                drawnItems.removeLayer(layer);
                const poly = L.polygon(latlngs, opts);
                drawnItems.addLayer(poly);
                return poly;
            }
            return layer;
        }

        function setupTransform(layer) {
            layer.options.interactive = true;

            try {
                layer.transform = new L.Handler.PathTransform(layer);
                layer.transform.enable({ rotation: true, scaling: true, uniformScaling: true, translation: true });
            } catch(e) {
                console.warn('Transform setup failed:', e);
            }

            layer.on('transformed', function() {
                populateLatLng(layer);
                const geojson = readShapeFromLayer(layer);
                document.getElementById('shape-input').value = JSON.stringify(geojson);
            });

            confirmBtn.classList.remove('hidden');
        }

        function populateLatLng(layer) {
            try {
                const geojson = readShapeFromLayer(layer);
                const centroid = turf.centroid(geojson);
                const lat = centroid.geometry.coordinates[1];
                const lng = centroid.geometry.coordinates[0];
                document.getElementById('lat-input').value = lat.toFixed(6);
                document.getElementById('lng-input').value = lng.toFixed(6);
                document.getElementById('lat-display').textContent = lat.toFixed(6);
                document.getElementById('lng-display').textContent = lng.toFixed(6);
                updateCentroidMarker(L.latLng(lat, lng));
            } catch(e) {}
        }

        map.on(L.Draw.Event.CREATED, function(event) {
            drawnItems.clearLayers();
            let layer = event.layer;

            drawnItems.addLayer(layer);
            layer = toPolygon(layer);
            const geoJson = layer.toGeoJSON();

            if (!isInsideBoundary(geoJson)) {
                layer.setStyle({ color: '#dc2626', weight: 3, fillOpacity: 0.2 });
                alertEl.classList.remove('hidden');
                currentLayer = null;
                return;
            }

            alertEl.classList.add('hidden');
            currentLayer = layer;
            populateLatLng(layer);
            setupTransform(layer);

            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        });

        map.on(L.Draw.Event.EDITED, function(event) {
            const layers = event.layers;
            layers.eachLayer(function(layer) {
                drawnItems.clearLayers();
                drawnItems.addLayer(layer);
                layer = toPolygon(layer);
                currentLayer = layer;
                populateLatLng(layer);
                setupTransform(layer);
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            });
        });

        map.on(L.Draw.Event.DRAWSTART, function() {
            drawnItems.clearLayers();
            currentLayer = null;
            if (centroidMarker) map.removeLayer(centroidMarker);
            centroidMarker = null;
            confirmBtn.classList.add('hidden');
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            alertEl.classList.add('hidden');
        });

        confirmBtn.addEventListener('click', confirmShape);

        @if(old('shape'))
        (function() {
            try {
                const raw = JSON.parse('{{ old('shape') }}');
                const geom = raw?.geometry || raw;
                const feats = geom.type ? geom : { type: 'Feature', geometry: geom, properties: {} };
                const geoLayer = L.geoJSON(feats, {
                    style: { color: '#6366f1', weight: 3, fillOpacity: 0.35 },
                });
                const layers = geoLayer.getLayers();
                if (layers.length > 0) {
                    const layer = layers[0];
                    drawnItems.addLayer(layer);
                    map.fitBounds(layer.getBounds().pad(0.1));
                    currentLayer = layer;
                    populateLatLng(layer);
                    setupTransform(layer);
                    confirmShape();
                }
            } catch(e) {}
        })();
        @endif
    </script>
</x-app-layout>