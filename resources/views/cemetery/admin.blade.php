<x-app-layout>
    @push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>
    <style>
        .map-container { height: calc(100vh - 320px); }
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
        .cemetery-header { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 12px 16px; display: flex; flex-wrap: wrap; gap: 12px; align-items: center; margin-bottom: 12px; }
        .cemetery-header select { padding: 8px 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 0.9rem; min-width: 200px; }
        .cemetery-header select:focus { outline: none; border-color: #059669; }
        .cemetery-header input { padding: 8px 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 0.9rem; }
        .cemetery-header input:focus { outline: none; border-color: #059669; }
        .tag { display: inline-block; padding: 2px 10px; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
        .tag-green { background: #d1fae5; color: #065f46; }
        .tag-gray { background: #f3f4f6; color: #6b7280; }
        @media (max-width: 768px) {
            .admin-layout { flex-direction: column; }
            .admin-layout > div { width: 100% !important; }
            .map-container { height: 400px; }
        }
    </style>
    @endpush

    <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-4">Cemetery Administration</h1>

        <!-- Cemetery Selector -->
        <div class="cemetery-header">
            <label class="font-semibold text-gray-700 text-sm">Cemetery:</label>
            <select id="cemeterySelect" onchange="switchCemetery(this.value)">
                <option value="">-- Select a cemetery --</option>
                @foreach($cemeteries as $c)
                    <option value="{{ $c->id }}" {{ $selectedCemetery && $selectedCemetery->id === $c->id ? 'selected' : '' }}>
                        {{ $c->name }} {!! $c->polygon ? '<span class="tag tag-green">&#10003;</span>' : '<span class="tag tag-gray">no polygon</span>' !!}
                    </option>
                @endforeach
                <option value="new">+ Create New Cemetery</option>
            </select>

            <div id="newCemeteryFields" style="display: {{ $cemeteries->isEmpty() ? 'flex' : 'none' }}; gap: 8px; align-items: center; flex: 1;">
                <input type="text" id="newCemeteryName" placeholder="Enter cemetery name..." class="flex-1">
                <button onclick="createCemetery()" class="btn btn-primary">Create</button>
            </div>

            <span class="flex-1"></span>
            <span id="currentCemeteryLabel" class="text-sm text-gray-500 font-medium">
                {{ $selectedCemetery ? $selectedCemetery->name : 'No cemetery selected' }}
            </span>
        </div>

        <div class="flex admin-layout gap-4">
            <!-- Map -->
            <div class="flex-1 flex flex-col" style="min-width:0;">
                <div class="map-container bg-white rounded-t-xl overflow-hidden shadow-sm">
                    <div id="map" class="w-full h-full"></div>
                </div>
                <div class="control-bar">
                    <span id="pointCount" class="text-sm font-medium text-gray-600">0 points</span>
                    <span class="text-gray-500">|</span>
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
                        <li class="text-gray-500 text-sm text-center py-4">Click on the map to add vertices</li>
                    </ol>
                </div>

                <div class="panel-card flex flex-wrap gap-2">
                    <button onclick="savePolygon()" id="btnSave" class="btn btn-primary flex-1" disabled>
                        <span id="saveText">Save Polygon</span>
                        <span id="saveSpinner" class="spinner" style="display:none"></span>
                    </button>
                    <button onclick="loadPolygon()" id="btnLoad" class="btn btn-secondary">
                        <span id="loadText">Load</span>
                        <span id="loadSpinner" class="spinner" style="display:none"></span>
                    </button>
                </div>

                <div class="panel-card">
                    <h3 class="font-semibold text-gray-900 mb-2">Export / Import</h3>
                    <div class="flex gap-2">
                        <button onclick="exportGeoJson()" class="btn btn-primary text-xs flex-1">Export GeoJSON</button>
                        <label class="btn btn-secondary text-xs flex-1 text-center cursor-pointer">
                            Import
                            <input type="file" id="importFile" accept=".geojson,.json" onchange="importGeoJson(event)" class="hidden">
                        </label>
                    </div>
                </div>

                <div class="panel-card">
                    <h3 class="font-semibold text-gray-900 mb-2">Quick Actions</h3>
                    <div class="flex gap-2">
                        <button onclick="seedGraves()" class="btn btn-warning text-xs flex-1">Seed Graves</button>
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
            <button onclick="closeGraveModal()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        </div>
        <input type="text" id="graveSearch" placeholder="Search graves..." oninput="filterGraves()" class="w-full px-3 py-2 border rounded-lg mb-3 text-sm">
        <div id="graveList" class="flex-1 overflow-y-auto text-sm"></div>
    </div>
</div>

@push('scripts')
<script>
    function showToast(msg, type) {
        const el = document.createElement('div');
        el.className = 'fixed top-4 right-4 z-[9999] px-5 py-3 rounded-lg text-white font-medium shadow-lg text-sm ' +
            (type === 'success' ? 'bg-emerald-600' : type === 'error' ? 'bg-red-600' : 'bg-blue-600');
        el.textContent = msg;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 3000);
    }

    let map, drawnPolygon, previewPolygon;
    let points = [];
    let markers = [];
    let polygonLayer = null;
    let previewLayer = null;
    let isFinished = false;
    let existingPolygonCoords = null;
    let currentCemeteryId = {{ $selectedCemetery ? $selectedCemetery->id : 'null' }};

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

        // Load any existing polygon for selected cemetery
        if (currentCemeteryId) {
            loadExistingPolygon(currentCemeteryId);
        }
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
        document.getElementById('coordList').innerHTML = '<li class="text-gray-500 text-sm text-center py-4">Click on the map to add vertices</li>';
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
            document.getElementById('coordList').innerHTML = '<li class="text-gray-500 text-sm text-center py-4">Click on the map to add vertices</li>';
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
            coords.push(coords[0]);
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

    // --- Cemetery Management ---

    function switchCemetery(value) {
        if (value === 'new') {
            document.getElementById('newCemeteryFields').style.display = 'flex';
            document.getElementById('newCemeteryName').focus();
            document.getElementById('currentCemeteryLabel').textContent = 'Creating new cemetery...';
            clearDrawing();
            currentCemeteryId = null;
            existingPolygonCoords = null;
            return;
        }

        document.getElementById('newCemeteryFields').style.display = 'none';

        if (!value) {
            clearDrawing();
            currentCemeteryId = null;
            existingPolygonCoords = null;
            document.getElementById('currentCemeteryLabel').textContent = 'No cemetery selected';
            return;
        }

        currentCemeteryId = parseInt(value);
        const label = document.getElementById('cemeterySelect').selectedOptions[0].text;
        document.getElementById('currentCemeteryLabel').textContent = label;
        clearDrawing();
        loadExistingPolygon(currentCemeteryId);
    }

    async function createCemetery() {
        const name = document.getElementById('newCemeteryName').value.trim();
        if (!name) {
            showToast('Please enter a cemetery name', 'error');
            return;
        }

        try {
            const res = await fetch('{{ route('cemetery.save') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ name }),
            });
            const data = await res.json();
            if (data.status === 'ok') {
                showToast('Cemetery created!', 'success');
                // Reload to update the dropdown
                location.reload();
            }
        } catch (e) {
            showToast('Failed to create cemetery', 'error');
        }
    }

    // --- Save ---
    async function savePolygon() {
        if (points.length < 3 || !isFinished) {
            showToast('Finish the polygon before saving', 'error');
            return;
        }

        if (!currentCemeteryId) {
            showToast('Select or create a cemetery first', 'error');
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
                    cemetery_id: currentCemeteryId,
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
        if (!currentCemeteryId) {
            showToast('Select a cemetery first', 'error');
            return;
        }

        document.getElementById('loadText').style.display = 'none';
        document.getElementById('loadSpinner').style.display = 'inline-block';

        try {
            const res = await fetch('{{ route('cemetery.polygon.get') }}?cemetery_id=' + currentCemeteryId);
            const data = await res.json();

            if (!data || !data.geojson) {
                showToast('No saved polygon found for this cemetery', 'info');
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

    async function loadExistingPolygon(cemeteryId) {
        try {
            const res = await fetch('{{ route('cemetery.polygon.get') }}?cemetery_id=' + cemeteryId);
            const data = await res.json();
            if (data && data.geojson) {
                existingPolygonCoords = data.geojson.coordinates[0].map(c => [c[1], c[0]]);
                // Display it on the map
                clearDrawing();
                existingPolygonCoords.forEach(c => addPoint(L.latLng(c[0], c[1])));
                isFinished = true;
                if (previewLayer) map.removeLayer(previewLayer);
                previewLayer = null;
                drawFilledPolygon();
                updateArea();
                document.getElementById('btnFinish').disabled = true;
                document.getElementById('btnSave').disabled = false;
            } else {
                existingPolygonCoords = null;
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

        const cemeteryName = document.getElementById('currentCemeteryLabel').textContent || 'Cemetery';

        const geojson = {
            type: 'FeatureCollection',
            features: [{
                type: 'Feature',
                geometry: { type: 'Polygon', coordinates: [coords] },
                properties: { name: cemeteryName, exported: new Date().toISOString() },
            }],
        };

        const blob = new Blob([JSON.stringify(geojson, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = (cemeteryName + '-boundary.geojson').replace(/[^a-zA-Z0-9]/g, '-').toLowerCase();
        a.click();
        URL.revokeObjectURL(url);
        showToast('GeoJSON exported!', 'success');
    }

    // --- Import ---
    async function importGeoJson(event) {
        const file = event.target.files[0];
        if (!file) return;

        if (!currentCemeteryId) {
            showToast('Select a cemetery first', 'error');
            event.target.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = async function(e) {
            try {
                const geojson = JSON.parse(e.target.result);

                const res = await fetch('{{ route('cemetery.import') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({
                        geojson: JSON.stringify(geojson),
                        cemetery_id: currentCemeteryId,
                    }),
                });
                const data = await res.json();
                if (data.status === 'ok') {
                    showToast(`Imported! Polygon: ${data.polygon_imported ? 'yes' : 'no'}, Graves: ${data.graves_imported}`, 'success');
                    if (data.polygon_imported) setTimeout(() => loadExistingPolygon(currentCemeteryId), 500);
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

    // --- Seed Graves ---
    async function seedGraves() {
        if (!currentCemeteryId) {
            showToast('Select a cemetery first', 'error');
            return;
        }
        if (!confirm('Seed sample graves inside this cemetery boundary?')) return;

        try {
            const res = await fetch('{{ route('cemetery.seed') }}?cemetery_id=' + currentCemeteryId);
            const data = await res.json();
            if (data.status === 'ok') {
                showToast(`Seeded ${data.count} graves!`, 'success');
            } else {
                showToast(data.message || 'Failed to seed graves', 'error');
            }
        } catch (e) {
            showToast('Failed to seed graves', 'error');
        }
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
            list.innerHTML = '<p class="text-gray-500 text-center py-8">No graves found.</p>';
            return;
        }
        list.innerHTML = graves.map(g => `
            <div class="flex items-center justify-between p-3 border-b border-gray-100 hover:bg-gray-50 rounded-lg">
                <div>
                    <div class="font-medium text-gray-900">${g.full_name}</div>
                    <div class="text-gray-500 text-xs">${g.plot_number || 'No plot'} ${g.section ? '— ' + g.section : ''}</div>
                </div>
                <span class="text-xs text-gray-500">[${g.latitude}, ${g.longitude}]</span>
            </div>
        `).join('');
    }

    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeGraveModal(); });

    initMap();
</script>
@endpush
</x-app-layout>
