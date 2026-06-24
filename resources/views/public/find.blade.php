@extends('layouts.public')

@section('title', 'Find a Loved One — Heritage Memorial Park')

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>
    <style>
        #map { height: 100%; width: 100%; }
        .find-wrap { position: fixed; inset: 0; top: 64px; }
        .status-bar { position: absolute; top: 16px; left: 50%; transform: translateX(-50%); z-index: 1000; padding: 8px 20px; border-radius: 9999px; font-size: 0.875rem; font-weight: 600; box-shadow: 0 4px 12px rgba(0,0,0,0.15); white-space: nowrap; pointer-events: none; }
        .status-bar.inside { background: #d1fae5; color: #065f46; border: 2px solid #059669; }
        .status-bar.outside { background: #fee2e2; color: #991b1b; border: 2px solid #dc2626; }
        .status-bar.unavailable { background: #fef3c7; color: #92400e; border: 2px solid #d97706; }
        .search-container { position: absolute; top: 76px; left: 50%; transform: translateX(-50%); z-index: 1000; width: 90%; max-width: 500px; transition: opacity 0.3s; }
        .search-container.disabled { opacity: 0.5; pointer-events: none; }
        .search-box { width: 100%; padding: 12px 16px 12px 44px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 1rem; background: white; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .search-box:focus { outline: none; border-color: #059669; }
        .search-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9ca3af; }
        .results-dropdown { position: absolute; top: 100%; left: 0; right: 0; margin-top: 4px; background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); max-height: 300px; overflow-y: auto; display: none; z-index: 1001; }
        .results-dropdown.show { display: block; }
        .result-item { padding: 10px 16px; border-bottom: 1px solid #f3f4f6; cursor: pointer; transition: background 0.15s; }
        .result-item:hover { background: #f0fdf4; }
        .result-item .name { font-weight: 600; color: #111827; }
        .result-item .meta { font-size: 0.8rem; color: #6b7280; }
        .result-item .badge { font-size: 0.75rem; background: #d1fae5; color: #065f46; padding: 2px 8px; border-radius: 9999px; }
        .detail-panel { position: absolute; bottom: 0; left: 0; right: 0; z-index: 1000; background: white; border-radius: 16px 16px 0 0; box-shadow: 0 -4px 20px rgba(0,0,0,0.15); padding: 20px; transform: translateY(100%); transition: transform 0.3s; max-height: 50vh; overflow-y: auto; }
        .detail-panel.show { transform: translateY(0); }
        .detail-panel .close-btn { position: absolute; top: 12px; right: 16px; font-size: 1.5rem; color: #9ca3af; cursor: pointer; }
        .detail-panel .close-btn:hover { color: #374151; }
        .detail-panel h2 { font-size: 1.25rem; font-weight: 700; color: #111827; margin-bottom: 4px; }
        .detail-panel .meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 12px; }
        .detail-panel .meta-item { background: #f9fafb; padding: 8px 12px; border-radius: 8px; }
        .detail-panel .meta-item label { font-size: 0.7rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; }
        .detail-panel .meta-item span { font-size: 0.9rem; font-weight: 500; color: #111827; display: block; }
        .btn-directions { display: inline-flex; align-items: center; gap: 6px; padding: 10px 20px; background: #059669; color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: background 0.15s; margin-top: 12px; }
        .btn-directions:hover { background: #047857; }
        .btn-directions:disabled { opacity: 0.5; cursor: not-allowed; }
        .gps-spinner { display: inline-block; width: 18px; height: 18px; border: 3px solid rgba(255,255,255,0.3); border-radius: 50%; border-top-color: white; animation: spin 0.8s linear infinite; margin-right: 8px; vertical-align: middle; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .outside-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.03); pointer-events: none; z-index: 500; }
        .leaflet-popup-content { margin: 10px 16px; }
        .leaflet-popup-content h3 { font-weight: 700; font-size: 1rem; margin-bottom: 4px; }
        .leaflet-popup-content .popup-meta { font-size: 0.8rem; color: #6b7280; }
        @media (min-width: 1024px) {
            .find-wrap { top: 80px; }
        }
        @media (max-width: 640px) {
            .status-bar { font-size: 0.75rem; padding: 6px 14px; top: 12px; }
            .search-container { top: 68px; }
            .detail-panel { padding: 16px; }
        }
    </style>
@endpush

@section('content')
    <div class="find-wrap">
        <div id="map"></div>

        <div id="statusBar" class="status-bar unavailable">
            <span id="gpsSpinner" class="gps-spinner"></span>
            <span id="statusText">Detecting your location...</span>
        </div>

        <div id="searchContainer" class="search-container disabled">
            <svg class="search-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/></svg>
            <input type="text" id="searchBox" class="search-box" placeholder="Search for a grave..." disabled>
            <div id="searchResults" class="results-dropdown"></div>
        </div>

        <div id="outsideOverlay" class="outside-overlay" style="display:none;"></div>

        <div id="detailPanel" class="detail-panel">
            <span class="close-btn" onclick="closeDetail()">&times;</span>
            <div id="detailContent"></div>
        </div>
    </div>
    <style> main { padding: 0; } </style>

    @push('scripts')
    <script>
        const map = L.map('map', {
            center: [16.5253, 121.1906],
            zoom: 18,
            minZoom: 15,
            maxZoom: 20,
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 20,
            attribution: '&copy; OpenStreetMap',
        }).addTo(map);

        let userLocation = null;
        let userMarker = null;
        let accuracyCircle = null;
        let isInside = false;
        let locationAvailable = false;
        let highlightedMarker = null;
        let polygonLayer = null;
        let cemeteryPolygon = null;
        let pathLayer = null;
        let pathStartMarker = null;
        let pathEndMarker = null;
        let pathTarget = null;
        let isRouting = false;
        let stateRestored = false;
        let watchId = null;
        let searchResultsData = [];
        let searchTimeout;

        async function loadCemeteryData() {
            try {
                const [polyRes, markersRes] = await Promise.all([
                    fetch('{{ route('cemetery.polygon.get') }}'),
                    fetch('{{ route('public.markers') }}'),
                ]);
                const polyData = await polyRes.json();

                if (polyData && polyData.geojson) {
                    cemeteryPolygon = polyData.geojson;
                    drawCemeteryPolygon();
                }
            } catch (e) {
                console.error('Failed to load cemetery data', e);
            }
        }

        function drawCemeteryPolygon() {
            if (!cemeteryPolygon || !cemeteryPolygon.coordinates) return;
            const coords = cemeteryPolygon.coordinates[0].map(c => [c[1], c[0]]);
            polygonLayer = L.polygon(coords, {
                color: '#059669',
                weight: 2,
                fillColor: '#059669',
                fillOpacity: 0.1,
            }).addTo(map);
            map.fitBounds(polygonLayer.getBounds().pad(0.1));
        }

        function onPositionUpdate(position) {
            userLocation = L.latLng(position.coords.latitude, position.coords.longitude);
            locationAvailable = true;

            if (userMarker) map.removeLayer(userMarker);
            if (accuracyCircle) map.removeLayer(accuracyCircle);

            const userIcon = L.divIcon({
                className: '',
                html: '<div style="width:20px;height:20px;background:#2563eb;border:3px solid #fff;border-radius:50%;box-shadow:0 2px 8px rgba(0,0,0,0.4);"><div style="width:8px;height:8px;background:#fff;border-radius:50%;position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);"></div></div>',
                iconSize: [20, 20],
                iconAnchor: [10, 10],
            });

            userMarker = L.marker([userLocation.lat, userLocation.lng], { icon: userIcon, zIndexOffset: 1000 }).addTo(map);

            accuracyCircle = L.circle([userLocation.lat, userLocation.lng], {
                radius: position.coords.accuracy || 50,
                color: '#2563eb',
                fillColor: '#2563eb',
                fillOpacity: 0.08,
                weight: 1,
            }).addTo(map);

            checkLocationAndUpdate();

            if (!map.getBounds().contains(userLocation)) {
                map.setView(userLocation, 18);
            }

            restoreState();
            if (pathTarget) {
                refreshPath();
            }
        }

        function onPositionError(error) {
            locationAvailable = false;
            let msg = 'Unable to detect your location';
            if (error.code === 1) msg = 'Location permission denied. Please enable location services.';
            else if (error.code === 2) msg = 'Location unavailable. Try again.';
            else if (error.code === 3) msg = 'Location request timed out. Retrying...';

            updateStatus(msg, 'unavailable', false);
            checkLocationAndUpdate();
        }

        function detectLocation() {
            updateStatus('Detecting your location...', 'unavailable', true);

            if (!navigator.geolocation) {
                updateStatus('Geolocation is not supported by your browser', 'unavailable', false);
                locationAvailable = false;
                checkLocationAndUpdate();
                return;
            }

            if (watchId !== null) {
                navigator.geolocation.clearWatch(watchId);
            }

            watchId = navigator.geolocation.watchPosition(
                onPositionUpdate,
                onPositionError,
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 5000,
                }
            );
        }

        function updateStatus(text, type, showSpinner) {
            const bar = document.getElementById('statusBar');
            const textEl = document.getElementById('statusText');
            const spinner = document.getElementById('gpsSpinner');

            bar.className = 'status-bar ' + type;
            textEl.textContent = text;
            spinner.style.display = showSpinner ? 'inline-block' : 'none';
        }

        function checkLocationAndUpdate() {
            const searchContainer = document.getElementById('searchContainer');
            const searchBox = document.getElementById('searchBox');
            const outsideOverlay = document.getElementById('outsideOverlay');

            if (!locationAvailable) {
                updateStatus('Please enable location services to search', 'unavailable', false);
                searchContainer.className = 'search-container disabled';
                searchBox.disabled = true;
                outsideOverlay.style.display = 'block';
                isInside = false;
                return;
            }

            const pt = turf.point([userLocation.lng, userLocation.lat]);

            if (cemeteryPolygon && cemeteryPolygon.coordinates) {
                const poly = turf.polygon(cemeteryPolygon.coordinates);
                isInside = turf.booleanPointInPolygon(pt, poly);
            } else {
                isInside = true;
            }

            if (isInside) {
                updateStatus('You are inside the cemetery. Search enabled.', 'inside', false);
                searchContainer.className = 'search-container';
                searchBox.disabled = false;
                outsideOverlay.style.display = 'none';
            } else {
                updateStatus('You are outside the cemetery. Search disabled.', 'outside', false);
                searchContainer.className = 'search-container disabled';
                searchBox.disabled = true;
                outsideOverlay.style.display = 'block';
            }
        }

        const searchBox = document.getElementById('searchBox');
        const searchResults = document.getElementById('searchResults');

        searchBox.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const q = this.value.trim();
            if (q.length < 2) {
                searchResults.classList.remove('show');
                return;
            }
            searchTimeout = setTimeout(() => performSearch(q), 200);
        });

        async function performSearch(q) {
            const res = await fetch('{{ route('public.search') }}?q=' + encodeURIComponent(q));
            searchResultsData = await res.json();

            if (searchResultsData.length === 0) {
                searchResults.innerHTML = '<div class="result-item" style="color:#9ca3af;text-align:center;">No results found</div>';
                searchResults.classList.add('show');
                return;
            }

            searchResults.innerHTML = searchResultsData.map(r => `
                <div class="result-item" onclick="flyToBurial(${r.lat}, ${r.lng}, '${r.name.replace(/'/g, "\\'")}', '${r.plot_number.replace(/'/g, "\\'")}', '${(r.section || '').replace(/'/g, "\\'")}', '${r.dates.replace(/'/g, "\\'")}')">
                    <div class="name">${r.name}</div>
                    <div class="meta">${r.plot_number || ''} ${r.section ? '— ' + r.section : ''}</div>
                </div>
            `).join('');

            searchResults.classList.add('show');
        }

        function saveState(name, plot, section, dates, lat, lng, hasPath) {
            sessionStorage.setItem('findState', JSON.stringify({ name, plot, section, dates, lat, lng, hasPath }));
        }

        function clearState() {
            sessionStorage.removeItem('findState');
        }

        function flyToBurial(lat, lng, name, plot, section, dates) {
            searchResults.classList.remove('show');
            map.flyTo([lat, lng], 20, { duration: 1 });

            if (highlightedMarker) map.removeLayer(highlightedMarker);

            const highlightIcon = L.divIcon({
                className: '',
                html: '<div style="width:22px;height:22px;background:#059669;border:3px solid #fff;border-radius:50%;box-shadow:0 2px 8px rgba(0,0,0,0.4);animation:pulse 1.5s infinite;"></div>',
                iconSize: [22, 22],
                iconAnchor: [11, 11],
            });

            highlightedMarker = L.marker([lat, lng], { icon: highlightIcon, zIndexOffset: 900 }).addTo(map);

            showDetailPanel(name, plot, section, dates, lat, lng);
            saveState(name, plot, section, dates, lat, lng);
        }

        function renderPath(data, endLat, endLng) {
            if (pathLayer) map.removeLayer(pathLayer);
            if (pathStartMarker) map.removeLayer(pathStartMarker);
            if (pathEndMarker) map.removeLayer(pathEndMarker);

            const pathCoords = data.path.geometry.coordinates.map(c => [c[1], c[0]]);

            pathLayer = L.polyline(pathCoords, {
                color: '#ea580c',
                weight: 4,
                opacity: 0.8,
                dashArray: '8, 12',
            }).addTo(map);

            const startIcon = L.divIcon({
                className: '',
                html: '<div style="width:16px;height:16px;background:#ea580c;border:2px solid #fff;border-radius:50%;box-shadow:0 1px 6px rgba(0,0,0,0.4);"></div>',
                iconSize: [16, 16],
                iconAnchor: [8, 8],
            });

            pathStartMarker = L.marker([pathCoords[0][0], pathCoords[0][1]], { icon: startIcon }).addTo(map);

            const endIcon = L.divIcon({
                className: '',
                html: '<div style="width:22px;height:22px;background:#059669;border:3px solid #fff;border-radius:50%;box-shadow:0 2px 8px rgba(0,0,0,0.4);"><div style="width:6px;height:6px;background:#fff;border-radius:50%;position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);"></div></div>',
                iconSize: [22, 22],
                iconAnchor: [11, 11],
            });

            pathEndMarker = L.marker([endLat, endLng], { icon: endIcon, zIndexOffset: 900 }).addTo(map);

            const distText = data.path.properties.distance_text;
            const walkTime = Math.ceil(data.path.properties.distance * 12 * 60);
            document.getElementById('pathInfo').innerHTML = distText + ' · ~' + walkTime + ' min walk via pathways';
        }

        async function findPath(endLat, endLng) {
            if (!userLocation || !isInside) return;

            pathTarget = { lat: endLat, lng: endLng };
            const state = JSON.parse(sessionStorage.getItem('findState') || '{}');
            if (state.name) saveState(state.name, state.plot, state.section, state.dates, endLat, endLng, true);

            const btn = document.querySelector('.btn-directions');
            btn.disabled = true;
            btn.textContent = 'Finding path...';

            try {
                const res = await fetch('{{ route('cemetery.find-path') }}?start_lat=' + userLocation.lat + '&start_lng=' + userLocation.lng + '&end_lat=' + endLat + '&end_lng=' + endLng);
                const data = await res.json();

                if (data.error) {
                    document.getElementById('pathInfo').textContent = data.error;
                    btn.disabled = false;
                    btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>Show Path';
                    return;
                }

                renderPath(data, endLat, endLng);
                map.flyTo([endLat, endLng], 20, { duration: 1.5 });

                btn.disabled = false;
                btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>Show Path';

            } catch (e) {
                pathTarget = null;
                document.getElementById('pathInfo').textContent = 'Failed to find path';
                btn.disabled = false;
                btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>Show Path';
            }
        }

        async function refreshPath() {
            if (!pathTarget || !userLocation || !isInside || isRouting) return;
            isRouting = true;
            try {
                const res = await fetch('{{ route('cemetery.find-path') }}?start_lat=' + userLocation.lat + '&start_lng=' + userLocation.lng + '&end_lat=' + pathTarget.lat + '&end_lng=' + pathTarget.lng);
                const data = await res.json();
                if (data.path) {
                    renderPath(data, pathTarget.lat, pathTarget.lng);
                }
            } catch (e) {}
            isRouting = false;
        }

        function showDetailPanel(name, plot, section, dates, lat, lng) {
            const panel = document.getElementById('detailPanel');
            const content = document.getElementById('detailContent');
            const canPath = locationAvailable && isInside;

            content.innerHTML = `
                <h2>${name}</h2>
                <div class="meta-grid">
                    <div class="meta-item">
                        <label>Section</label>
                        <span>${section || '—'}</span>
                    </div>
                    <div class="meta-item">
                        <label>Plot Number</label>
                        <span>${plot || '—'}</span>
                    </div>
                </div>
                ${canPath ? '<button class="btn-directions" onclick="findPath(' + lat + ', ' + lng + ')"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>Show Path</button><div id="pathInfo" class="text-sm text-gray-500 mt-2"></div>' : ''}
                ${!isInside ? '<p class="text-sm text-gray-400 mt-3">Visit the cemetery to find paths</p>' : ''}
            `;

            panel.classList.add('show');
        }

        function closeDetail() {
            document.getElementById('detailPanel').classList.remove('show');
            pathTarget = null;
            clearState();
            if (highlightedMarker) {
                map.removeLayer(highlightedMarker);
                highlightedMarker = null;
            }
            if (pathLayer) {
                map.removeLayer(pathLayer);
                pathLayer = null;
            }
            if (pathStartMarker) {
                map.removeLayer(pathStartMarker);
                pathStartMarker = null;
            }
            if (pathEndMarker) {
                map.removeLayer(pathEndMarker);
                pathEndMarker = null;
            }
        }

        function restoreState() {
            const saved = sessionStorage.getItem('findState');
            if (!saved || stateRestored) return;
            stateRestored = true;
            try {
                const g = JSON.parse(saved);
                showDetailPanel(g.name, g.plot, g.section, g.dates, g.lat, g.lng);
                map.flyTo([g.lat, g.lng], 20, { duration: 1 });
                if (g.hasPath && userLocation && isInside) {
                    findPath(g.lat, g.lng);
                }
            } catch (e) {}
        }

        loadCemeteryData().then(() => restoreState());
        detectLocation();

        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDetail(); });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.search-container')) {
                searchResults.classList.remove('show');
            }
        });

        const style = document.createElement('style');
        style.textContent = '@keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.2); } }';
        document.head.appendChild(style);
    </script>
    @endpush
@endsection
