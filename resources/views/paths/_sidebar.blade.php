<style>
    .section { margin-bottom: 16px; }
    .section h3 { font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.05em; }
    .form-row { display: flex; flex-direction: column; gap: 6px; }
    .form-row input, .form-row select { padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 13px; width: 100%; box-sizing: border-box; }
    .form-row input:focus, .form-row select:focus { outline: none; border-color: #059669; ring: 2px solid #d1fae5; }
    .btn { padding: 6px 12px; border: none; border-radius: 4px; font-size: 13px; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; }
    .btn-primary { background: #059669; color: #fff; }
    .btn-primary:hover { background: #047857; }
    .btn-success { background: #16a34a; color: #fff; }
    .btn-success:hover { background: #15803d; }
    .btn-warning { background: #d97706; color: #fff; }
    .btn-warning:hover { background: #b45309; }
    .btn-danger { background: #dc2626; color: #fff; }
    .btn-danger:hover { background: #b91c1c; }
    .btn-info { background: #2563eb; color: #fff; }
    .btn-info:hover { background: #1d4ed8; }
    .btn-sm { padding: 4px 8px; font-size: 12px; }
    .btn-outline { background: #fff; color: #374151; border: 1px solid #d1d5db; }
    .btn-outline:hover { background: #f9fafb; }
    .status-message { display: none; padding: 8px 12px; border-radius: 4px; font-size: 13px; font-weight: 500; }
    .status-message.success { display: block; background: #d1fae5; color: #065f46; }
    .status-message.error { display: block; background: #fee2e2; color: #991b1b; }
    .status-message.info { display: block; background: #dbeafe; color: #1e40af; }
    .mode-btn.active { ring: 2px solid #059669; background: #d1fae5; }
    .stat-card { background: #f9fafb; border-radius: 6px; padding: 8px; text-align: center; font-size: 12px; color: #6b7280; }
    .stat-card strong { display: block; font-size: 18px; color: #111827; margin-top: 2px; }
</style>

<div style="display:flex;flex-direction:column;gap:12px;max-height:560px;overflow-y:auto;padding-right:4px;">

    {{-- Header --}}
    <div>
        <h2 style="font-size:16px;font-weight:700;color:#111827;margin:0;">Pathways</h2>
        <p style="font-size:12px;color:#6b7280;margin:2px 0 0;">Dijkstra shortest path</p>
    </div>

    {{-- Mode Indicator --}}
    <div id="modeIndicator" style="padding:6px 10px;border-radius:4px;font-size:12px;font-weight:600;text-align:center;background:#e5e7eb;color:#374151;">
        VIEW
    </div>

    {{-- Toolbar --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:4px;">
        <button class="btn btn-outline mode-btn" data-mode="view" onclick="setMode('view')" style="font-size:12px;">👁 View</button>
        <button class="btn btn-primary mode-btn" data-mode="node" onclick="setMode('node')" style="font-size:12px;">➕ Node</button>
        <button class="btn btn-success mode-btn" data-mode="edge" onclick="setMode('edge')" style="font-size:12px;">🔗 Edge</button>
        <button class="btn btn-info mode-btn" data-mode="draw" onclick="setMode('draw')" style="font-size:12px;">✏️ Draw</button>
        <button class="btn btn-outline" onclick="clearPath()" style="font-size:12px;grid-column:span 2;">🗑 Clear Path</button>
    </div>

    {{-- Stats --}}
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px;">
        <div class="stat-card">
            <span>Nodes</span>
            <strong id="nodeCount">0</strong>
        </div>
        <div class="stat-card">
            <span>Edges</span>
            <strong id="edgeCount">0</strong>
        </div>
        <div class="stat-card">
            <span>Total KM</span>
            <strong id="totalDistance">0</strong>
        </div>
    </div>

    {{-- Add Node --}}
    <div class="section">
        <h3>Add Node (Form)</h3>
        <div class="form-row">
            <input type="number" id="nodeLat" placeholder="Latitude" step="any">
            <input type="number" id="nodeLng" placeholder="Longitude" step="any">
            <input type="text" id="nodeName" placeholder="Name / label (optional)">
            <select id="nodeType">
                <option value="waypoint">Waypoint</option>
                <option value="entrance">Entrance</option>
                <option value="facility">Facility</option>
                <option value="section">Section</option>
            </select>
            <button class="btn btn-primary" onclick="handleAddNodeForm()" style="width:100%;justify-content:center;">➕ Add Node</button>
            <small style="color:#6b7280;font-size:11px;">Or click map in Node mode</small>
        </div>
    </div>

    {{-- Add Edge --}}
    <div class="section">
        <h3>Add Edge</h3>
        <div class="form-row">
            <select id="edgeFrom"><option value="">From node</option></select>
            <select id="edgeTo"><option value="">To node</option></select>
            <div style="display:flex;gap:8px;align-items:center;">
                <input type="number" id="edgeWeight" placeholder="Weight (km)" step="any" style="flex:1;">
                <label style="font-size:12px;white-space:nowrap;"><input type="checkbox" id="autoWeightForm" checked> Auto</label>
            </div>
            <label style="font-size:12px;"><input type="checkbox" id="bidirectionalForm" checked> Bidirectional</label>
            <button class="btn btn-success" onclick="handleAddEdgeForm()" style="width:100%;justify-content:center;">🔗 Add Edge</button>
            <small style="color:#6b7280;font-size:11px;">Or click two nodes in Edge mode</small>
        </div>
    </div>

    {{-- Pathfinding --}}
    <div class="section">
        <h3>Find Shortest Path</h3>
        <div class="form-row">
            <select id="startNode"><option value="">Start node</option></select>
            <select id="endNode"><option value="">End node</option></select>
            <button class="btn btn-warning" onclick="handleFindPath()" style="width:100%;justify-content:center;">🚀 Find Path</button>
        </div>
    </div>

    {{-- Path Result --}}
    <div id="pathResult" class="section" style="display:none;background:#fff7ed;border:1px solid #fdba74;border-radius:6px;padding:10px;">
        <h3 style="color:#c2410c;">Path Result</h3>
        <div style="font-size:13px;">
            <div><strong>Distance:</strong> <span id="pathDistance">0</span></div>
            <div><strong>Steps:</strong> <span id="pathSteps">0</span></div>
            <div id="pathNodesList" style="margin-top:4px;font-size:12px;color:#374151;"></div>
        </div>
    </div>

    {{-- Node List --}}
    <div class="section">
        <h3>Nodes</h3>
        <div id="nodeList" style="max-height:120px;overflow-y:auto;font-size:13px;border:1px solid #e5e7eb;border-radius:4px;padding:6px;">
            <div style="color:#9ca3af;font-size:13px;">No nodes yet</div>
        </div>
    </div>

    {{-- Edge List --}}
    <div class="section">
        <h3>Edges</h3>
        <div id="edgeList" style="max-height:120px;overflow-y:auto;font-size:13px;border:1px solid #e5e7eb;border-radius:4px;padding:6px;">
            <div style="color:#9ca3af;font-size:13px;">No edges yet</div>
        </div>
    </div>

    {{-- Data Management --}}
    <div class="section">
        <h3>Data</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:4px;">
            <button class="btn btn-primary btn-sm" onclick="exportData()" style="width:100%;justify-content:center;">⬇ Export</button>
            <button class="btn btn-success btn-sm" onclick="document.getElementById('importFile').click()" style="width:100%;justify-content:center;">⬆ Import</button>
            <input type="file" id="importFile" accept=".json" style="display:none" onchange="importData()">
            <button class="btn btn-danger btn-sm" onclick="resetAll()" style="width:100%;justify-content:center;grid-column:span 2;">🔄 Reset All</button>
        </div>
    </div>

    {{-- Keyboard Shortcuts --}}
    <div style="font-size:11px;color:#9ca3af;padding-top:4px;border-top:1px solid #e5e7eb;">
        1: View | 2: Node | 3: Edge | 4: Draw | Enter: Find Path
    </div>

    {{-- Status Message --}}
    <div id="statusMessage" class="status-message">Ready</div>
</div>
