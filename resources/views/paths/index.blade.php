<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Pathways & Navigation') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div style="display: flex; gap: 20px;">
                        <div style="width: 300px; flex-shrink: 0;">
                            @include('paths._sidebar')
                        </div>
                        <div id="map" style="flex: 1; height: 600px; border: 2px solid #e5e7eb; border-radius: 4px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('head')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <style>
            .path-popup .leaflet-popup-content { margin: 8px 12px; }
            .path-result { background: #fff7ed; border: 2px dashed #f97316; }
        </style>
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            const GRAPH_DATA = @json($graphData);
            const CSRF = "{{ csrf_token() }}";
            const ROUTES = {
                storeNode: "{{ route('paths.nodes.store') }}",
                storeEdge: "{{ route('paths.edges.store') }}",
                findPath: "{{ route('paths.find') }}",
                destroyNode: "{{ route('paths.nodes.destroy', ':id') }}",
                destroyEdge: "{{ route('paths.edges.destroy', ':id') }}",
                export: "{{ route('paths.export') }}",
                import: "{{ route('paths.import') }}",
                reset: "{{ route('paths.reset') }}",
            };

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

            satellite.addTo(map);

            const edgeLayer = L.layerGroup().addTo(map);
            const nodeLayer = L.layerGroup().addTo(map);
            const pathLayer = L.layerGroup().addTo(map);

            let mode = 'view';
            let selectedNode = null;
            let startNode = null;
            let endNode = null;
            let drawCounter = 0;

            const nodeColors = {
                entrance: '#059669',
                waypoint: '#3b82f6',
                facility: '#8b5cf6',
                section: '#d97706',
            };

            function drawAll() {
                edgeLayer.clearLayers();
                nodeLayer.clearLayers();
                pathLayer.clearLayers();

                GRAPH_DATA.edges.forEach(edge => {
                    const from = GRAPH_DATA.nodes.find(n => n.id === edge.from_node_id);
                    const to = GRAPH_DATA.nodes.find(n => n.id === edge.to_node_id);
                    if (from && to) {
                        const polyline = L.polyline([
                            [from.lat, from.lng],
                            [to.lat, to.lng]
                        ], {
                            color: '#6b7280',
                            weight: 2,
                            opacity: 0.5,
                        }).addTo(edgeLayer);
                        polyline.bindPopup(`
                            <b>${edge.from_node_id} → ${edge.to_node_id}</b><br>
                            Weight: ${edge.weight ? edge.weight.toFixed(4) + ' km' : 'auto'}<br>
                            Type: ${edge.path_type}<br>
                            <button onclick="deleteEdge(${edge.id})" style="color:#dc2626;cursor:pointer;border:none;background:none;font-size:12px;">Delete</button>
                        `);
                    }
                });

                GRAPH_DATA.nodes.forEach(node => {
                    const color = nodeColors[node.type] || '#3b82f6';
                    const marker = L.circleMarker([node.lat, node.lng], {
                        radius: 10,
                        fillColor: color,
                        color: '#fff',
                        weight: 2,
                        opacity: 1,
                        fillOpacity: 0.9,
                    }).addTo(nodeLayer);

                    const label = node.label || node.name || `Node ${node.id}`;
                    marker.bindPopup(`
                        <div class="path-popup">
                            <b>${label}</b><br>
                            <span style="font-size:12px;color:#6b7280;">
                                ID: ${node.id} | ${node.type}<br>
                                ${node.lat.toFixed(6)}, ${node.lng.toFixed(6)}
                            </span><br>
                            <button onclick="setStart(${node.id})" style="color:#059669;cursor:pointer;border:none;background:none;font-size:12px;">Set Start</button>
                            <button onclick="setEnd(${node.id})" style="color:#d97706;cursor:pointer;border:none;background:none;font-size:12px;">Set End</button>
                            <button onclick="deleteNode(${node.id})" style="color:#dc2626;cursor:pointer;border:none;background:none;font-size:12px;">Delete</button>
                        </div>
                    `);

                    marker.on('click', function() {
                        if (mode === 'edge' && selectedNode !== null && selectedNode !== node.id) {
                            addEdge(selectedNode, node.id);
                            selectedNode = null;
                        } else if (mode === 'edge') {
                            selectedNode = node.id;
                            showMessage('Selected: ' + label + '. Click another node to connect.', 'info');
                        }
                    });
                });
            }

            function setStart(id) {
                startNode = id;
                document.getElementById('startNode').value = id;
                map.closePopup();
                showMessage('Start node set to: ' + getNodeLabel(id), 'success');
            }

            function setEnd(id) {
                endNode = id;
                document.getElementById('endNode').value = id;
                map.closePopup();
                showMessage('End node set to: ' + getNodeLabel(id), 'success');
            }

            function getNodeLabel(id) {
                const node = GRAPH_DATA.nodes.find(n => n.id === id);
                if (!node) return 'Unknown';
                return node.label || node.name || 'Node ' + id;
            }

            function showMessage(text, type) {
                const el = document.getElementById('statusMessage');
                el.textContent = text;
                el.className = 'status-message ' + type;
                el.style.display = 'block';
                setTimeout(() => { el.style.display = 'none'; }, 5000);
            }

            function setMode(newMode) {
                mode = newMode;
                selectedNode = null;
                lastDrawnNode = null;
                drawCounter = 0;
                document.getElementById('modeIndicator').textContent = mode.toUpperCase();

                document.querySelectorAll('.mode-btn').forEach(btn => {
                    btn.classList.toggle('active', btn.dataset.mode === mode);
                });

                map.getContainer().style.cursor = (mode === 'node' || mode === 'draw') ? 'crosshair' : '';
            }

            map.on('click', function(e) {
                if (mode === 'node') {
                    const name = prompt('Node name/label (optional):');
                    const types = ['waypoint', 'entrance', 'facility', 'section'];
                    const type = prompt('Type (' + types.join(', ') + '):') || 'waypoint';

                    fetch(ROUTES.storeNode, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                        body: JSON.stringify({
                            lat: e.latlng.lat,
                            lng: e.latlng.lng,
                            name: name || null,
                            label: name || null,
                            type: type,
                        })
                    })
                    .then(r => r.json())
                    .then(node => {
                        GRAPH_DATA.nodes.push(node);
                        drawAll();
                        populateSelects();
                        updateStats();
                        showMessage('Node added: ' + (node.label || 'Node ' + node.id), 'success');
                    })
                    .catch(() => showMessage('Failed to create node', 'error'));
                }

                if (mode === 'draw') {
                    drawCounter++;
                    const name = 'Draw point ' + drawCounter;

                    fetch(ROUTES.storeNode, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                        body: JSON.stringify({
                            lat: e.latlng.lat,
                            lng: e.latlng.lng,
                            name: name,
                            label: name,
                            type: 'waypoint',
                        })
                    })
                    .then(r => r.json())
                    .then(node => {
                        GRAPH_DATA.nodes.push(node);
                        drawAll();
                        populateSelects();
                        updateStats();
                        showMessage('Waypoint added: ' + name, 'success');
                    })
                    .catch(() => showMessage('Failed to create node', 'error'));
                }
            });

            function addEdge(fromId, toId) {
                const body = {
                    from_node_id: fromId,
                    to_node_id: toId,
                    is_bidirectional: document.getElementById('bidirectionalForm').checked,
                };

                fetch(ROUTES.storeEdge, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify(body),
                })
                .then(r => r.json())
                .then(edge => {
                    GRAPH_DATA.edges.push(edge);
                    drawAll();
                    updateStats();
                    showMessage('Edge added: ' + fromId + ' → ' + toId, 'success');
                })
                .catch(() => showMessage('Failed to create edge', 'error'));
            }

            function handleAddNodeForm() {
                const lat = parseFloat(document.getElementById('nodeLat').value);
                const lng = parseFloat(document.getElementById('nodeLng').value);
                if (isNaN(lat) || isNaN(lng)) {
                    showMessage('Invalid coordinates', 'error');
                    return;
                }
                const name = document.getElementById('nodeName').value || null;
                const type = document.getElementById('nodeType').value;

                fetch(ROUTES.storeNode, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify({ lat, lng, name, label: name, type }),
                })
                .then(r => r.json())
                .then(node => {
                    GRAPH_DATA.nodes.push(node);
                    drawAll();
                    populateSelects();
                    updateStats();
                    document.getElementById('nodeLat').value = '';
                    document.getElementById('nodeLng').value = '';
                    document.getElementById('nodeName').value = '';
                    showMessage('Node added successfully', 'success');
                })
                .catch(() => showMessage('Failed to create node', 'error'));
            }

            function handleAddEdgeForm() {
                const from = parseInt(document.getElementById('edgeFrom').value);
                const to = parseInt(document.getElementById('edgeTo').value);
                if (!from || !to || from === to) {
                    showMessage('Select two different nodes', 'error');
                    return;
                }
                const autoWeight = document.getElementById('autoWeightForm').checked;
                const weightInput = document.getElementById('edgeWeight').value;
                const body = {
                    from_node_id: from,
                    to_node_id: to,
                    is_bidirectional: document.getElementById('bidirectionalForm').checked,
                };
                if (!autoWeight && weightInput) {
                    body.weight = parseFloat(weightInput);
                }

                fetch(ROUTES.storeEdge, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify(body),
                })
                .then(r => r.json())
                .then(edge => {
                    GRAPH_DATA.edges.push(edge);
                    drawAll();
                    updateStats();
                    showMessage('Edge added successfully', 'success');
                })
                .catch(() => showMessage('Failed to create edge', 'error'));
            }

            function handleFindPath() {
                const start = parseInt(document.getElementById('startNode').value);
                const end = parseInt(document.getElementById('endNode').value);
                if (!start || !end || start === end) {
                    showMessage('Select two different nodes', 'error');
                    return;
                }

                fetch(ROUTES.findPath + '?start_id=' + start + '&end_id=' + end, {
                    headers: { 'X-CSRF-TOKEN': CSRF },
                })
                .then(r => r.json())
                .then(result => {
                    if (result.error) {
                        showMessage(result.error, 'error');
                        return;
                    }
                    pathLayer.clearLayers();
                    const coords = result.nodeIds.map(id => {
                        const node = GRAPH_DATA.nodes.find(n => n.id === id);
                        return [node.lat, node.lng];
                    });

                    L.polyline(coords, {
                        color: '#f97316',
                        weight: 6,
                        opacity: 0.9,
                        dashArray: '10, 5',
                    }).addTo(pathLayer);

                    coords.forEach((coord, i) => {
                        L.circleMarker(coord, {
                            radius: 8,
                            fillColor: '#f97316',
                            color: '#fff',
                            weight: 2,
                            fillOpacity: 0.9,
                        }).addTo(pathLayer).bindPopup('Step ' + (i + 1) + ': ' + getNodeLabel(result.nodeIds[i]));
                    });

                    document.getElementById('pathResult').style.display = 'block';
                    document.getElementById('pathDistance').textContent = result.distance.toFixed(4) + ' km';
                    document.getElementById('pathSteps').textContent = result.nodeIds.length + ' nodes';
                    document.getElementById('pathNodesList').innerHTML = result.nodeIds.map((id, i) =>
                        '<div>' + (i + 1) + '. ' + getNodeLabel(id) + ' <span style="color:#6b7280;font-size:12px;">(ID: ' + id + ')</span></div>'
                    ).join('');

                    showMessage('Path found! Distance: ' + result.distance.toFixed(4) + ' km', 'success');
                })
                .catch(() => showMessage('Failed to find path', 'error'));
            }

            function clearPath() {
                pathLayer.clearLayers();
                document.getElementById('pathResult').style.display = 'none';
            }

            function deleteNode(id) {
                if (!confirm('Delete this node and all connected edges?')) return;
                const url = ROUTES.destroyNode.replace(':id', id);
                fetch(url, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF } })
                .then(r => {
                    if (r.ok) {
                        GRAPH_DATA.nodes = GRAPH_DATA.nodes.filter(n => n.id !== id);
                        GRAPH_DATA.edges = GRAPH_DATA.edges.filter(e => e.from_node_id !== id && e.to_node_id !== id);
                        drawAll();
                        populateSelects();
                        updateStats();
                        showMessage('Node deleted', 'info');
                    }
                })
                .catch(() => showMessage('Failed to delete node', 'error'));
            }

            function deleteEdge(id) {
                const url = ROUTES.destroyEdge.replace(':id', id);
                fetch(url, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF } })
                .then(r => {
                    if (r.ok) {
                        GRAPH_DATA.edges = GRAPH_DATA.edges.filter(e => e.id !== id);
                        drawAll();
                        populateSelects();
                        updateStats();
                        showMessage('Edge deleted', 'info');
                    }
                })
                .catch(() => showMessage('Failed to delete edge', 'error'));
                map.closePopup();
            }

            function populateSelects() {
                const nodes = GRAPH_DATA.nodes;
                const selectors = ['edgeFrom', 'edgeTo', 'startNode', 'endNode'];
                selectors.forEach(id => {
                    const sel = document.getElementById(id);
                    if (!sel) return;
                    const val = sel.value;
                    sel.innerHTML = '<option value="">Select node</option>';
                    nodes.forEach(n => {
                        const label = n.label || n.name || 'Node ' + n.id;
                        sel.innerHTML += '<option value="' + n.id + '">' + label + ' (' + n.type + ')</option>';
                    });
                    if (val) sel.value = val;
                });

                const list = document.getElementById('nodeList');
                if (list) {
                    if (nodes.length === 0) {
                        list.innerHTML = '<div style="color:#9ca3af;font-size:13px;">No nodes yet</div>';
                    } else {
                        list.innerHTML = nodes.map(n => {
                            const label = n.label || n.name || 'Node ' + n.id;
                            return '<div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid #f3f4f6;">' +
                                '<div><strong>' + label + '</strong><br><span style="font-size:11px;color:#6b7280;">ID:' + n.id + ' | ' + n.type + '</span></div>' +
                                '<button onclick="deleteNode(' + n.id + ')" style="color:#dc2626;cursor:pointer;border:none;background:none;font-size:13px;">✕</button>' +
                                '</div>';
                        }).join('');
                    }
                }

                const edgeList = document.getElementById('edgeList');
                if (edgeList) {
                    if (GRAPH_DATA.edges.length === 0) {
                        edgeList.innerHTML = '<div style="color:#9ca3af;font-size:13px;">No edges yet</div>';
                    } else {
                        edgeList.innerHTML = GRAPH_DATA.edges.map(e => {
                            const fromLabel = getNodeLabel(e.from_node_id);
                            const toLabel = getNodeLabel(e.to_node_id);
                            return '<div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid #f3f4f6;">' +
                                '<div><strong>' + fromLabel + '</strong> → <strong>' + toLabel + '</strong><br><span style="font-size:11px;color:#6b7280;">' + (e.weight ? e.weight.toFixed(4) + ' km' : 'auto') + ' | ' + e.path_type + '</span></div>' +
                                '<button onclick="deleteEdge(' + e.id + ')" style="color:#dc2626;cursor:pointer;border:none;background:none;font-size:13px;">✕</button>' +
                                '</div>';
                        }).join('');
                    }
                }
            }

            function updateStats() {
                document.getElementById('nodeCount').textContent = GRAPH_DATA.nodes.length;
                document.getElementById('edgeCount').textContent = GRAPH_DATA.edges.length;
                const total = GRAPH_DATA.edges.reduce((sum, e) => sum + (e.weight || 0), 0);
                document.getElementById('totalDistance').textContent = total.toFixed(4);
            }

            function exportData() {
                fetch(ROUTES.export, { headers: { 'X-CSRF-TOKEN': CSRF } })
                .then(r => r.json())
                .then(data => {
                    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'pathways_' + new Date().toISOString().slice(0, 10) + '.json';
                    a.click();
                    URL.revokeObjectURL(url);
                    showMessage('Data exported!', 'success');
                });
            }

            function importData() {
                const file = document.getElementById('importFile').files[0];
                if (!file) { showMessage('Select a file first', 'error'); return; }
                const reader = new FileReader();
                reader.onload = function(e) {
                    try {
                        const data = JSON.parse(e.target.result);
                        fetch(ROUTES.import, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                            body: JSON.stringify(data),
                        })
                        .then(r => r.json())
                        .then(() => {
                            location.reload();
                        });
                    } catch (err) {
                        showMessage('Invalid JSON file', 'error');
                    }
                };
                reader.readAsText(file);
            }

            function resetAll() {
                if (!confirm('Delete ALL nodes and edges? This cannot be undone.')) return;
                fetch(ROUTES.reset, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF },
                })
                .then(r => r.json())
                .then(() => {
                    location.reload();
                });
            }

            document.addEventListener('keydown', function(e) {
                if (e.key === '1') setMode('view');
                if (e.key === '2') setMode('node');
                if (e.key === '3') setMode('edge');
                if (e.key === '4') setMode('draw');
                if (e.key === 'Enter' && document.activeElement?.tagName !== 'INPUT' && document.activeElement?.tagName !== 'SELECT') {
                    handleFindPath();
                }
            });

            drawAll();
            populateSelects();
            updateStats();
            document.getElementById('modeIndicator').textContent = 'VIEW';
        </script>
    @endpush
</x-app-layout>
