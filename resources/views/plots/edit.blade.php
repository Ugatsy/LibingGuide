<x-app-layout>
    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Plot: {{ $plot->plot_number }}</h1>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="grid lg:grid-cols-5 gap-8">
                    <div class="lg:col-span-3">
                        <p class="text-sm font-medium text-gray-700 mb-2">Click a shape to edit it, or draw a new rectangle to replace it</p>
                        <div id="map" style="height: 500px; border: 2px solid #e5e7eb; border-radius: 6px;"></div>
                        <button id="confirm-shape-btn" class="hidden mt-2 w-full px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-md hover:bg-emerald-700">Confirm Shape</button>
                        <p class="text-xs text-gray-500 mt-2">Use the handles to rotate/scale, or draw a new shape. Click <strong>Confirm Shape</strong> to finalize.</p>
                    </div>
                    <div class="lg:col-span-2">
                        <form method="POST" action="{{ route('plots.update', $plot) }}">
                            @csrf @method('PUT')
                            <input type="hidden" name="lat" id="lat-input" value="{{ old('lat', $plot->lat) }}">
                            <input type="hidden" name="lng" id="lng-input" value="{{ old('lng', $plot->lng) }}">
                            <input type="hidden" name="shape" id="shape-input" value="{{ old('shape', is_string($plot->shape) ? $plot->shape : json_encode($plot->shape)) }}">

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Plot Number</label>
                                    <input type="text" name="plot_number" value="{{ old('plot_number', $plot->plot_number) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Section / Block</label>
                                    <input type="text" name="section" value="{{ old('section', $plot->section) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Lot Type</label>
                                        <select name="lot_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="individual" {{ ($plot->lot_type ?? 'individual') === 'individual' ? 'selected' : '' }}>Individual</option>
                                            <option value="family" {{ $plot->lot_type === 'family' ? 'selected' : '' }}>Family</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Dimension</label>
                                        <input type="text" name="dimension" value="{{ old('dimension', $plot->dimension) }}" placeholder="e.g. 1.5m × 2.5m" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Latitude</label>
                                        <input type="text" id="lat-display" readonly class="mt-1 block w-full rounded-md bg-gray-50 border-gray-300 text-sm text-gray-600" value="{{ $plot->lat }}">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Longitude</label>
                                        <input type="text" id="lng-display" readonly class="mt-1 block w-full rounded-md bg-gray-50 border-gray-300 text-sm text-gray-600" value="{{ $plot->lng }}">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Capacity</label>
                                        <input type="number" name="capacity" value="{{ old('capacity', $plot->capacity) }}" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Price (₱)</label>
                                        <input type="number" step="0.01" name="price" value="{{ old('price', $plot->price) }}" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Status</label>
                                    <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @foreach(['available','reserved','occupied','full'] as $s)
                                            <option value="{{ $s }}" {{ $plot->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Notes</label>
                                    <textarea name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $plot->notes) }}</textarea>
                                </div>
                                <div class="flex items-center justify-end gap-4 pt-2">
                                    <a href="{{ route('plots.index') }}" class="text-gray-600 hover:text-gray-900">Cancel</a>
                                    <button type="submit" id="submit-btn" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Update</button>
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
            L.geoJSON(geom, {
                style: { color: '#059669', weight: 3, fillColor: '#059669', fillOpacity: 0.08 },
            }).addTo(map);
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

        function populateLatLng(layer) {
            try {
                const geojson = readShapeFromLayer(layer);
                const centroid = turf.centroid(geojson);
                const lat = centroid.geometry.coordinates[1];
                const lng = centroid.geometry.coordinates[0];
                document.getElementById('lat-input').value = lat.toFixed(6);
                document.getElementById('lng-input').value = lng.toFixed(6);
                try {
                    document.getElementById('lat-display').textContent = lat.toFixed(6);
                    document.getElementById('lng-display').textContent = lng.toFixed(6);
                } catch(e) {}
                updateCentroidMarker(L.latLng(lat, lng));
            } catch(e) {}
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
                return;
            }

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

        function loadExistingShape() {
            const existing = document.getElementById('shape-input').value;
            if (!existing) return false;
            try {
                const raw = JSON.parse(existing);
                const geom = raw?.geometry || raw;
                if (geom?.coordinates) {
                    const feats = geom.type ? geom : { type: 'Feature', geometry: geom, properties: {} };
                    const geoLayer = L.geoJSON(feats, {
                        style: { color: '#6366f1', weight: 4, fillOpacity: 0.3 },
                    });
                    const layers = geoLayer.getLayers();
                    if (layers.length > 0) {
                        let layer = layers[0];
                        drawnItems.addLayer(layer);
                        layer = toPolygon(layer);
                        map.fitBounds(layer.getBounds().pad(0.1));
                        currentLayer = layer;
                        populateLatLng(layer);
                        setupTransform(layer);
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                        return true;
                    }
                }
            } catch(e) {}
            return false;
        }

        loadExistingShape();

        @if($plot->lat && $plot->lng)
        if (!currentLayer) {
            L.circleMarker([{{ $plot->lat }}, {{ $plot->lng }}], {
                radius: 8, color: '#6366f1', fillColor: '#6366f1', fillOpacity: 0.4, weight: 3,
            }).addTo(drawnItems);
            map.setView([{{ $plot->lat }}, {{ $plot->lng }}], 19);
        }
        @endif

        map.on(L.Draw.Event.CREATED, function(event) {
            drawnItems.clearLayers();
            let layer = event.layer;
            drawnItems.addLayer(layer);
            layer = toPolygon(layer);
            const geoJson = layer.toGeoJSON();

            if (!isInsideBoundary(geoJson)) {
                layer.setStyle({ color: '#dc2626', weight: 3, fillOpacity: 0.2 });
                return;
            }

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
        });

        confirmBtn.addEventListener('click', confirmShape);
    </script>
</x-app-layout>