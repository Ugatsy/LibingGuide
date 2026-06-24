# Dijkstra Pathfinding — Laravel Adaptation Guide

This document adapts the generic Dijkstra pathfinding implementation for **LibingGuide (Heritage Memorial Park)**, mapping each step to the existing Laravel + Leaflet + Alpine.js architecture.

---

## How This Differs from the Generic Version

| Generic Approach | Laravel Adaptation |
|---|---|
| Standalone `index.html` | Blade layout (`layouts/app.blade.php`) |
| Plain JS classes | PHP Service classes + Eloquent Models |
| `localStorage` persistence | MySQL database (migrations + Eloquent) |
| JS modules (ES modules) | Blade view with inline `<script>` (follows existing `plots/index.blade.php` pattern) |
| File-based data import/export | Laravel Import/Export (Excel/JSON) or database seeders |
| No auth | Uses existing `auth` middleware |
| CSS in separate file | Tailwind utility classes (inline, following existing pattern) |

---

## Phase 1: Database & Models (instead of JSON files)

### Step 1: Create Migrations

**`database/migrations/XXXX_XX_XX_XXXXXX_create_path_nodes_table.php`**

```php
Schema::create('path_nodes', function (Blueprint $table) {
    $table->id();
    $table->string('name')->nullable();
    $table->string('label')->nullable(); // e.g. "Entrance", "Section A", "Restroom"
    $table->decimal('lat', 10, 8);
    $table->decimal('lng', 11, 8);
    $table->string('type')->default('waypoint'); // entrance, waypoint, facility, section
    $table->timestamps();
});
```

**`database/migrations/XXXX_XX_XX_XXXXXX_create_path_edges_table.php`**

```php
Schema::create('path_edges', function (Blueprint $table) {
    $table->id();
    $table->foreignId('from_node_id')->constrained('path_nodes')->cascadeOnDelete();
    $table->foreignId('to_node_id')->constrained('path_nodes')->cascadeOnDelete();
    $table->decimal('weight', 10, 4)->nullable(); // auto-calculated if null
    $table->string('path_type')->default('walkway'); // walkway, road, stairs, ramp
    $table->boolean('is_bidirectional')->default(true);
    $table->timestamps();
});
```

### Step 2: Create Eloquent Models

**`app/Models/PathNode.php`**

```php
class PathNode extends Model
{
    protected $fillable = ['name', 'label', 'lat', 'lng', 'type'];

    public function outgoingEdges(): HasMany
    {
        return $this->hasMany(PathEdge::class, 'from_node_id');
    }

    public function incomingEdges(): HasMany
    {
        return $this->hasMany(PathEdge::class, 'to_node_id');
    }
}
```

**`app/Models/PathEdge.php`**

```php
class PathEdge extends Model
{
    protected $fillable = ['from_node_id', 'to_node_id', 'weight', 'path_type', 'is_bidirectional'];

    public function fromNode(): BelongsTo
    {
        return $this->belongsTo(PathNode::class, 'from_node_id');
    }

    public function toNode(): BelongsTo
    {
        return $this->belongsTo(PathNode::class, 'to_node_id');
    }
}
```

---

## Phase 2: Backend Service Classes (instead of `dijkstra.js`)

### Step 3: Dijkstra Algorithm Service

**`app/Services/DijkstraService.php`**

```php
class DijkstraService
{
    /**
     * Find shortest path between two path nodes.
     * Returns ['path' => [PathNode, ...], 'distance' => float, 'nodeIds' => [id, ...]]
     */
    public function findShortestPath(PathNode $start, PathNode $end): ?array
    {
        // Standard Dijkstra implementation
        // 1. Load all edges (bidirectional handling)
        // 2. Priority queue (SplPriorityQueue or array-based)
        // 3. Distance tracking, predecessor tracking
        // 4. Reconstruct path from end to start
        // 5. Return path nodes + total distance
    }

    /**
     * Calculate Haversine distance between two lat/lng points.
     */
    public function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        // Haversine formula, returns km
    }

    /**
     * Auto-calculate weight for an edge based on node positions.
     */
    public function autoCalculateWeight(PathNode $from, PathNode $to): float
    {
        return $this->calculateDistance($from->lat, $from->lng, $to->lat, $to->lng);
    }
}
```

### Step 4: Path Management Service

**`app/Services/PathManagerService.php`**

```php
class PathManagerService
{
    public function __construct(
        private DijkstraService $dijkstra
    ) {}

    public function createNode(array $data): PathNode { }

    public function createEdge(int $fromId, int $toId, ?float $weight = null, string $type = 'walkway'): PathEdge
    {
        if ($weight === null) {
            $from = PathNode::findOrFail($fromId);
            $to = PathNode::findOrFail($toId);
            $weight = $this->dijkstra->autoCalculateWeight($from, $to);
        }
        // Create edge + bidirectional if applicable
    }

    public function findPath(int $startId, int $endId): ?array
    {
        $start = PathNode::findOrFail($startId);
        $end = PathNode::findOrFail($endId);
        return $this->dijkstra->findShortestPath($start, $end);
    }

    public function getGraphData(): array
    {
        // Returns all nodes + edges for frontend rendering
        return [
            'nodes' => PathNode::all()->toArray(),
            'edges' => PathEdge::with('fromNode', 'toNode')->get()->toArray(),
        ];
    }

    public function exportJson(): string { }
    public function importJson(array $data): void { }
}
```

---

## Phase 3: Controller & Routes

### Step 5: PathController

**`app/Http/Controllers/PathController.php`**

```php
class PathController extends Controller
{
    public function __construct(private PathManagerService $pathManager) {}

    public function index(): View
    {
        $graphData = $this->pathManager->getGraphData();
        return view('paths.index', compact('graphData'));
    }

    public function storeNode(Request $request): JsonResponse
    {
        $node = $this->pathManager->createNode($request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'name' => 'nullable|string|max:255',
            'label' => 'nullable|string|max:255',
            'type' => 'nullable|string|in:entrance,waypoint,facility,section',
        ]));
        return response()->json($node, 201);
    }

    public function storeEdge(Request $request): JsonResponse
    {
        $edge = $this->pathManager->createEdge(
            $request->input('from_node_id'),
            $request->input('to_node_id'),
            $request->input('weight'),
            $request->input('path_type', 'walkway'),
        );
        return response()->json($edge->load('fromNode', 'toNode'), 201);
    }

    public function findPath(Request $request): JsonResponse
    {
        $result = $this->pathManager->findPath(
            $request->input('start_id'),
            $request->input('end_id'),
        );
        return $result
            ? response()->json($result)
            : response()->json(['error' => 'No path found'], 404);
    }

    public function destroyNode(PathNode $pathNode): JsonResponse
    {
        $pathNode->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function destroyEdge(PathEdge $pathEdge): JsonResponse
    {
        $pathEdge->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function export(): JsonResponse
    {
        return response()->json($this->pathManager->getGraphData());
    }

    public function import(Request $request): JsonResponse
    {
        $this->pathManager->importJson($request->validate([
            'nodes' => 'required|array',
            'edges' => 'required|array',
        ]));
        return response()->json(['message' => 'Imported']);
    }
}
```

### Step 6: Register Routes

**Add to `routes/web.php`** (inside the `auth` group):

```php
Route::resource('paths', PathController::class)->only(['index']);
Route::post('paths/nodes', [PathController::class, 'storeNode'])->name('paths.nodes.store');
Route::post('paths/edges', [PathController::class, 'storeEdge'])->name('paths.edges.store');
Route::get('paths/find-path', [PathController::class, 'findPath'])->name('paths.find');
Route::delete('paths/nodes/{pathNode}', [PathController::class, 'destroyNode'])->name('paths.nodes.destroy');
Route::delete('paths/edges/{pathEdge}', [PathController::class, 'destroyEdge'])->name('paths.edges.destroy');
Route::get('paths/export', [PathController::class, 'export'])->name('paths.export');
Route::post('paths/import', [PathController::class, 'import'])->name('paths.import');
```

---

## Phase 4: Blade View (following `plots/index.blade.php` pattern)

### Step 7: Create `resources/views/paths/index.blade.php`

Follows the exact same structure as `plots/index.blade.php` — inline Leaflet JS, Tailwind styling, sidebar + map layout.

| Generic Element | Blade Equivalent |
|---|---|
| `adminUI.setMode('node')` | Mode buttons that call inline JS functions |
| `adminUI.handleAddNode()` | Form submission via `POST /paths/nodes` |
| `adminUI.handleFindPath()` | AJAX call to `GET /paths/find-path` |
| Leaflet circle markers | `L.circleMarker()` (same as plot markers) |
| Route polylines | `L.polyline()` with dashed pattern for path result |

**Key template sections:**

```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Pathways & Navigation</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div style="display: flex; gap: 20px;">
                        <!-- Sidebar: 300px -->
                        <div style="width: 300px; flex-shrink: 0;">
                            @include('paths._sidebar')
                        </div>
                        <!-- Map: flex: 1 -->
                        <div id="map" style="flex: 1; height: 600px; border: 2px solid #e5e7eb; border-radius: 4px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet includes -->
    @push('head')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @endpush
    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            // Inline JS (same pattern as plots/index.blade.php)
            const GRAPH_DATA = @json($graphData);
            const CSRF = "{{ csrf_token() }}";
            const map = L.map('map', { ... });

            // Node markers (L.circleMarker)
            // Edge polylines (L.polyline)
            // Mode switching (add-node, add-edge, find-path)
            // Click handlers
            // Path result rendering (dashed orange polyline)
        </script>
    @endpush
</x-app-layout>
```

### Step 8: Sidebar Partial `resources/views/paths/_sidebar.blade.php`

Contains:
- Stats box (node count, edge count, total path km)
- Mode toggle buttons (Node / Edge / Path)
- Add node form (lat, lng, name, type)
- Add edge form (from select, to select, auto-weight toggle)
- Start/End node selectors + Find Path button
- Path result display (distance, node list)
- Node list with delete buttons
- Edge list with delete buttons
- Export / Import / Reset buttons

---

## Phase 5: Admin Navigation Integration

### Step 9: Add Nav Link

**In `resources/views/layouts/navigation.blade.php`, add under the "Services" dropdown:**

```blade
<a href="{{ route('paths.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-800 {{ request()->routeIs('paths.*') ? 'bg-emerald-50 text-emerald-800 font-medium' : '' }}">Pathways</a>
```

---

## Phase 6: Public-Facing Pathfinding (Optional)

### Step 10: Public Route & View

A public pathfinding feature could be added to help visitors navigate the cemetery:

- `GET /navigate` → Public view with map + start/end selectors (no editing)
- Uses same `DijkstraService` but with a read-only frontend
- Could embed in the existing `public/find.blade.php` or as a standalone page

---

## Phase 7: Testing

### Step 11: PHPUnit Tests

**`tests/Unit/Services/DijkstraServiceTest.php`**

```php
class DijkstraServiceTest extends TestCase
{
    public function test_calculates_haversine_distance(): void { }
    public function test_finds_shortest_path_in_simple_graph(): void { }
    public function test_returns_null_when_no_path_exists(): void { }
    public function test_handles_bidirectional_edges(): void { }
    public function test_handles_disconnected_components(): void { }
}
```

**`tests/Feature/PathControllerTest.php`**

```php
class PathControllerTest extends TestCase
{
    public function test_admin_can_create_node(): void { }
    public function test_admin_can_create_edge(): void { }
    public function test_admin_can_find_path(): void { }
    public function test_unauthenticated_user_cannot_access(): void { }
}
```

---

## Implementation Order (Recommended)

| # | Task | Files to Create/Modify |
|---|------|----------------------|
| 1 | Migration: path_nodes | `database/migrations/*_create_path_nodes_table.php` |
| 2 | Migration: path_edges | `database/migrations/*_create_path_edges_table.php` |
| 3 | Model: PathNode | `app/Models/PathNode.php` |
| 4 | Model: PathEdge | `app/Models/PathEdge.php` |
| 5 | Service: DijkstraService | `app/Services/DijkstraService.php` |
| 6 | Service: PathManagerService | `app/Services/PathManagerService.php` |
| 7 | Controller: PathController | `app/Http/Controllers/PathController.php` |
| 8 | Route registration | `routes/web.php` (add auth group routes) |
| 9 | Admin view: paths/index | `resources/views/paths/index.blade.php` |
| 10 | Admin partial: _sidebar | `resources/views/paths/_sidebar.blade.php` |
| 11 | Nav link update | `resources/views/layouts/navigation.blade.php` |
| 12 | Unit tests | `tests/Unit/Services/DijkstraServiceTest.php` |
| 13 | Feature tests | `tests/Feature/PathControllerTest.php` |

---

## Key Code Pattern Reference

The existing `plots/index.blade.php` is the **template to follow** for the paths view:
- Inline Leaflet JS (no separate JS files)
- AJAX calls to Laravel routes with `fetch()` + CSRF token
- Leaflet CDN (not npm)
- Tailwind for layout

The Dijkstra logic stays **server-side in PHP** — the frontend only sends/receives JSON.
