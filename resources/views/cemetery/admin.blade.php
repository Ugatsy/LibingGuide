<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Cemetery Boundary Tool</h2>
            <a href="{{ route('public.find') }}" class="text-sm text-emerald-700 hover:text-emerald-600 underline">View Public Map</a>
        </div>
    </x-slot>

    @push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>
    <style>
        .map-container { height: calc(100vh - 220px); }
        .coords-list { max-height: 200px; overflow-y: auto; }
        .coords-list li { padding: 4px 8px; border-bottom: 1px solid #f3f4f6; font-size: 0.8rem; display: flex; justify-content: space-between; align-items: center; }
        .coords-list li:hover { background: #f9fafb; }
        .btn { padding: 0.5rem 1rem; border-radius: 0.5rem; font-weight: 600; font-size: 0.875rem; border: none; cursor: pointer; transition: all 0.15s; }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .btn-primary { background: #059669; color: white; }
        .btn-primary:hover:not(:disabled) { background: #047857; }
        .btn-danger { background: #dc2626; color: white; }
        .btn-danger:hover:not(:disabled) { background: #b91c1c; }
        .btn-warning { background: #d97706; color: white; }
        .btn-warning:hover:not(:disabled) { background: #b45309; }
        .btn-secondary { background: #6b7280; color: white; }
        .btn-secondary:hover:not(:disabled) { background: #4b5563; }
        .spinner { display: inline-block; width: 20px; height: 20px; border: 3px solid rgba(255,255,255,0.3); border-radius: 50%; border-top-color: white; animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .control-bar { background: white; border-radius: 0 0 12px 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 12px 16px; display: flex; flex-wrap: wrap; gap: 8px; align-items: center; border-top: 3px solid #059669; }
        .panel-card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 16px; }
        @media (max-width: 768px) {
            .admin-layout { flex-direction: column; }
            .admin-layout > div { width: 100% !important; }
            .map-container { height: 400px; }
        }
    </style>
    @endpush

    <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex admin-layout gap-4">
            <!-- Map -->
            <div class="flex-1 flex flex-col" style="min-width:0;">
                <div class="map-container bg-white rounded-t-xl overflow-hidden shadow-sm">
                    <div id="map" class="w-full h-full"></div>
                </div>
                <div class="control-bar">
                    <span id="pointCount" class="text-sm font-medium text-gray-600">0 points</span>
                    <span class="text-gray-300">|</span>
                    <span id="areaDisplay" class="text-sm font-semibold text-emerald-700">Area: —</span>
                    <span class="flex-1"></span>
                    <button onclick="finishPolygon()" id="btnFinish" class="btn btn-primary" disabled>Finish Polygon</button>
                    <button onclick="clearDrawing()" class="btn btn-danger">Clear All</button>
                </div>
            </div>

            <!-- Side Panel -->
            <div class="w-80 flex flex-col gap-3" style="min-width:280px;">
                <div class="panel-card">
                    <h3 class="font-semibold text-gray-900 mb-2">Coordinates</h3>
                    <ol id="coordList" class="coords-list text-gray-600">
                        <li class="text-gray-400 text-sm text-center py-4">Click on the map to add vertices</li>
                    </ol>
                </div>

                <div class="panel-card flex flex-wrap gap-2">
                    <button onclick="savePolygon()" id="btnSave" class="btn btn-primary flex-1" disabled>
                        <span id="saveText">Save Polygon</span>
                        <span id="saveSpinner" class="spinner" style="display:none"></span>
                    </button>
                    <button onclick="loadPolygon()" id="btnLoad" class="btn btn-secondary">
                        <span id="loadText">Load Existing</span>
                        <span id="loadSpinner" class="spinner" style="display:none"></span>
                    </button>
                </div>

                <div class="panel-card">
                    <h3 class="font-semibold text-gray-900 mb-2">Export / Import</h3>
                    <div class="flex gap-2">
                        <button onclick="exportGeoJson()" class="btn btn-primary text-xs flex-1">Export GeoJSON</button>
                        <label class="btn btn-secondary text-xs flex-1 text-center cursor-pointer">
                            Import GeoJSON
                            <input type="file" id="importFile" accept=".geojson,.json" onchange="importGeoJson(event)" class="hidden">
                        </label>
                    </div>
                </div>

                <div class="panel-card">
                    <h3 class="font-semibold text-gray-900 mb-2">Quick Actions</h3>
                    <div class="flex gap-2">
                        <a href="{{ route('cemetery.seed') }}" class="btn btn-warning text-xs flex-1 text-center" onclick="return confirm('Seed 15 sample graves inside the boundary?')">Seed Graves</a>
                        <button onclick="graveManager()" class="btn btn-secondary text-xs flex-1">Manage Graves</button>
                    </div>
                </div>

                <div class="panel-card text-xs text-gray-500">
                    <p>Draw the cemetery boundary by clicking on the map. Minimum <strong>3 points</strong> required. Click "Finish Polygon" to close the shape before saving.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Grave Manager Modal -->
<div id="graveModal" class="fixed inset-0 bg-black/50 z-[9998] hidden items-center justify-center" onclick="if(event.target===this)closeGraveModal()">
    <div class="bg-white rounded-2xl p-6 max-w-2xl w-full mx-4 max-h-[80vh] flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-900">Grave Markers</h2>
            <button onclick="closeGraveModal()" class="text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
        </div>
        <input type="text" id="graveSearch" placeholder="Search graves..." oninput="filterGraves()" class="w-full px-3 py-2 border rounded-lg mb-3 text-sm">
        <div id="graveList" class="flex-1 overflow-y-auto text-sm"></div>
    </div>
</div>

@push('scripts')
<script>
    // --- Toast ---
    function showToast(msg, type) {
        const el = document.createElement('div');
        el.className = 'fixed top-4 right-4 z-[9999] px-5 py-3 rounded-lg text-white font-medium shadow-lg text-sm ' +
            (type === 'success' ? 'bg-emerald-600' : type === 'error' ? 'bg-red-600' : 'bg-blue-600');
        el.textContent = msg;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 3000);
    }

        // --- Map State ---
        let map, drawnPolygon, previewPolygon;
        let points = [];
        let markers = [];
        let polygonLayer = null;
        let previewLayer = null;
        let isFinished = false;
        let existingPolygonCoords = null;

        function initMap() {
            map = L.map('map', {
                center: [16.5253, 121.1906],
                zoom: 18,
                minZoom: 15,
                maxZoom: 20,
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 20,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            }).addTo(map);

            map.on('click', onMapClick);

            // Load any existing polygon
            loadExistingPolygon();
        }

        function onMapClick(e) {
            if (isFinished) {
                showToast('Click "Clear All" to start a new drawing', 'info');
                return;
            }
            addPoint(e.latlng);
        }

        function addPoint(latlng) {
            points.push(latlng);
            updateUI();

            const marker = L.circleMarker([latlng.lat, latlng.lng], {
                radius: 6,
                color: '#059669',
                fillColor: '#059669',
                fillOpacity: 1,
                weight: 2,
                opacity: 1,
            }).addTo(map);

            marker._idx = points.length - 1;
            marker.bindTooltip((points.length).toString(), { permanent: true, direction: 'top', offset: [0, -10], className: 'vertex-label' });
            markers.push(marker);

            // Update preview
            updatePreview();
        }

        function updatePreview() {
            if (previewLayer) map.removeLayer(previewLayer);
            if (points.length < 2) return;

            const coords = points.map(p => [p.lat, p.lng]);
            const poly = L.polyline(coords, { color: '#059669', weight: 2, dashArray: '5,10' }).addTo(map);
            previewLayer = poly;

            if (points.length >= 3 && isFinished) {
                drawFilledPolygon();
            }

            document.getElementById('btnFinish').disabled = points.length < 3;
        }

        function finishPolygon() {
            if (points.length < 3) {
                showToast('Need at least 3 points to create a polygon', 'error');
                return;
            }
            isFinished = true;
            if (previewLayer) map.removeLayer(previewLayer);
            previewLayer = null;
            drawFilledPolygon();
            updateArea();
            document.getElementById('btnFinish').disabled = true;
            document.getElementById('btnSave').disabled = false;
            showToast('Polygon closed! You can now save.', 'success');
        }

        function drawFilledPolygon() {
            if (polygonLayer) map.removeLayer(polygonLayer);
            const coords = points.map(p => [p.lat, p.lng]);
            polygonLayer = L.polygon(coords, {
                color: '#059669',
                weight: 2,
                fillColor: '#059669',
                fillOpacity: 0.15,
            }).addTo(map);
            map.fitBounds(polygonLayer.getBounds().pad(0.1));
        }

        function clearDrawing() {
            points = [];
            markers.forEach(m => map.removeLayer(m));
            markers = [];
            if (polygonLayer) map.removeLayer(polygonLayer);
            polygonLayer = null;
            if (previewLayer) map.removeLayer(previewLayer);
            previewLayer = null;
            isFinished = false;
            document.getElementById('btnFinish').disabled = true;
            document.getElementById('btnSave').disabled = true;
            document.getElementById('areaDisplay').textContent = 'Area: —';
            document.getElementById('pointCount').textContent = '0 points';
            document.getElementById('coordList').innerHTML = '<li class="text-gray-400 text-sm text-center py-4">Click on the map to add vertices</li>';
        }

        function removePoint(idx) {
            if (isFinished) {
                showToast('Click "Clear All" to start over', 'info');
                return;
            }
            if (idx < 0 || idx >= points.length) return;
            points.splice(idx, 1);
            map.removeLayer(markers[idx]);
            markers.splice(idx, 1);
            // Re-index markers
            markers.forEach((m, i) => {
                m._idx = i;
                m.unbindTooltip();
                m.bindTooltip((i + 1).toString(), { permanent: true, direction: 'top', offset: [0, -10], className: 'vertex-label' });
            });
            if (previewLayer) map.removeLayer(previewLayer);
            previewLayer = null;
            updatePreview();
            updateUI();
        }

        function updateUI() {
            document.getElementById('pointCount').textContent = points.length + ' points';

            if (points.length === 0) {
                document.getElementById('coordList').innerHTML = '<li class="text-gray-400 text-sm text-center py-4">Click on the map to add vertices</li>';
                return;
            }

            const list = document.getElementById('coordList');
            list.innerHTML = '';
            points.forEach((p, i) => {
                const li = document.createElement('li');
                li.innerHTML = `
                    <span><strong>${i + 1}.</strong> ${p.lat.toFixed(6)}, ${p.lng.toFixed(6)}</span>
                    <button onclick="removePoint(${i})" class="text-red-500 hover:text-red-700 text-xs font-bold ml-2" title="Remove point">&times;</button>
                `;
                list.appendChild(li);
            });
        }

        function updateArea() {
            if (points.length < 3) return;
            try {
                const coords = points.map(p => [p.lng, p.lat]);
                coords.push(coords[0]); // Close ring
                const polygon = turf.polygon([coords]);
                const areaSqm = turf.area(polygon);
                const areaHa = areaSqm / 10000;
                document.getElementById('areaDisplay').textContent =
                    `Area: ${areaSqm.toFixed(2)} sq m (${areaHa.toFixed(4)} ha)`;
                window._lastAreaSqm = areaSqm;
                window._lastAreaHa = areaHa;
            } catch (e) {
                console.error('Area calculation error:', e);
            }
        }

        // --- Save ---
        async function savePolygon() {
            if (points.length < 3 || !isFinished) {
                showToast('Finish the polygon before saving', 'error');
                return;
            }

            document.getElementById('saveText').style.display = 'none';
            document.getElementById('saveSpinner').style.display = 'inline-block';
            document.getElementById('btnSave').disabled = true;

            const coords = points.map(p => [p.lng, p.lat]);
            coords.push(coords[0]);
            const geojson = JSON.stringify({ type: 'Polygon', coordinates: [coords] });

            try {
                const res = await fetch('{{ route('cemetery.polygon.save') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({
                        geojson: geojson,
                        area_sqm: window._lastAreaSqm || 0,
                        area_hectares: window._lastAreaHa || 0,
                    }),
                });
                const data = await res.json();
                if (data.status) {
                    showToast('Polygon saved successfully!', 'success');
                }
            } catch (e) {
                showToast('Failed to save polygon', 'error');
            }

            document.getElementById('saveText').style.display = 'inline';
            document.getElementById('saveSpinner').style.display = 'none';
            document.getElementById('btnSave').disabled = false;
        }

        // --- Load ---
        async function loadPolygon() {
            document.getElementById('loadText').style.display = 'none';
            document.getElementById('loadSpinner').style.display = 'inline-block';

            try {
                const res = await fetch('{{ route('cemetery.polygon.get') }}');
                const data = await res.json();

                if (!data || !data.geojson) {
                    showToast('No saved polygon found', 'info');
                    document.getElementById('loadText').style.display = 'inline';
                    document.getElementById('loadSpinner').style.display = 'none';
                    return;
                }

                clearDrawing();

                const coords = data.geojson.coordinates[0].map(c => [c[1], c[0]]);
                coords.forEach(c => addPoint(L.latLng(c[0], c[1])));
                isFinished = true;
                if (previewLayer) map.removeLayer(previewLayer);
                previewLayer = null;
                drawFilledPolygon();
                updateArea();
                document.getElementById('btnFinish').disabled = true;
                document.getElementById('btnSave').disabled = false;
                showToast('Polygon loaded for editing!', 'success');
            } catch (e) {
                showToast('Failed to load polygon', 'error');
            }

            document.getElementById('loadText').style.display = 'inline';
            document.getElementById('loadSpinner').style.display = 'none';
        }

        async function loadExistingPolygon() {
            try {
                const res = await fetch('{{ route('cemetery.polygon.get') }}');
                const data = await res.json();
                if (data && data.geojson) {
                    existingPolygonCoords = data.geojson.coordinates[0].map(c => [c[1], c[0]]);
                }
            } catch (e) {}
        }

        // --- Export ---
        function exportGeoJson() {
            if (!polygonLayer && !existingPolygonCoords) {
                showToast('No polygon to export', 'error');
                return;
            }

            let coords;
            if (polygonLayer) {
                coords = points.map(p => [p.lng, p.lat]);
                coords.push(coords[0]);
            } else if (existingPolygonCoords) {
                coords = existingPolygonCoords.map(c => [c[1], c[0]]);
                coords.push(coords[0]);
            } else {
                showToast('No polygon data available', 'error');
                return;
            }

            const geojson = {
                type: 'FeatureCollection',
                features: [{
                    type: 'Feature',
                    geometry: { type: 'Polygon', coordinates: [coords] },
                    properties: { name: 'Cemetery Boundary', exported: new Date().toISOString() },
                }],
            };

            const blob = new Blob([JSON.stringify(geojson, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'cemetery-boundary.geojson';
            a.click();
            URL.revokeObjectURL(url);
            showToast('GeoJSON exported!', 'success');
        }

        // --- Import ---
        async function importGeoJson(event) {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = async function(e) {
                try {
                    const geojson = JSON.parse(e.target.result);

                    const res = await fetch('{{ route('cemetery.import') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ geojson: JSON.stringify(geojson) }),
                    });
                    const data = await res.json();
                    if (data.status === 'ok') {
                        showToast(`Imported! Polygon: ${data.polygon_imported ? 'yes' : 'no'}, Graves: ${data.graves_imported}`, 'success');
                        if (data.polygon_imported) setTimeout(loadPolygon, 500);
                    } else {
                        showToast('Import failed: ' + (data.message || 'unknown'), 'error');
                    }
                } catch (err) {
                    showToast('Invalid GeoJSON file', 'error');
                }
            };
            reader.readAsText(file);
            event.target.value = '';
        }

        // --- Grave Manager ---
        const graveData = @json($graves);

        function graveManager() {
            const modal = document.getElementById('graveModal');
            modal.style.display = 'flex';
            renderGraves(graveData);
        }

        function closeGraveModal() {
            document.getElementById('graveModal').style.display = 'none';
        }

        function filterGraves() {
            const q = document.getElementById('graveSearch').value.toLowerCase();
            const filtered = graveData.filter(g =>
                g.full_name.toLowerCase().includes(q) ||
                (g.plot_number && g.plot_number.toLowerCase().includes(q)) ||
                (g.section && g.section.toLowerCase().includes(q))
            );
            renderGraves(filtered);
        }

        function renderGraves(graves) {
            const list = document.getElementById('graveList');
            if (graves.length === 0) {
                list.innerHTML = '<p class="text-gray-400 text-center py-8">No graves found.</p>';
                return;
            }
            list.innerHTML = graves.map(g => `
                <div class="flex items-center justify-between p-3 border-b border-gray-100 hover:bg-gray-50 rounded-lg">
                    <div>
                        <div class="font-medium text-gray-900">${g.full_name}</div>
                        <div class="text-gray-500 text-xs">${g.plot_number || 'No plot'} ${g.section ? '— ' + g.section : ''}</div>
                    </div>
                    <span class="text-xs text-gray-400">[${g.latitude}, ${g.longitude}]</span>
                </div>
            `).join('');
        }

        // Close modal on Escape
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeGraveModal(); });

        // Initialize map on page load
        initMap();
    </script>
    @endpush
</x-app-layout>
