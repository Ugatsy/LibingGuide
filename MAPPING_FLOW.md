# Heritage Memorial Park — Mapping Flow

> Engineer's workflow for setting up the cemetery map: boundary → pathways → burial plots.

## Overview

The Engr role follows a 3-step process to build the cemetery map:

```
1. Cemetery Polygons  ──→  Draw the cemetery boundary on satellite view
         ↓
2. Map Pathing       ──→  Add walkway/road nodes and connect them
         ↓
3. Burial Plots      ──→  Place individual plots with shape, lot type, availability
```

---

## Step 1: Cemetery Polygons

**Route:** `GET /cemetery/admin`
**Nav:** Map → Cemetery Polygons
**View:** Leaflet satellite map with polygon drawing tools

### Purpose

Define the physical boundary of the cemetery grounds on the satellite map. This creates the container that all subsequent mapping (paths, plots) lives inside.

### Workflow

1. Open the **Cemetery Map** admin page
2. Click vertices on the satellite imagery to outline the cemetery perimeter
3. The polygon auto-calculates:
   - **Area in sqm** (using Turf.js)
   - **Area in hectares**
4. Click **Save Polygon** to persist the boundary
5. The boundary is stored as GeoJSON in `cemetery_polygons` table

### What Gets Created

| Table | Record |
|-------|--------|
| `cemetery_polygons` | 1 row with GeoJSON polygon, area_sqm, area_hectares |

---

## Step 2: Map Pathing

**Route:** `GET /paths`
**Nav:** Map → Map Pathing
**View:** Same satellite map with node/edge editing tools

### Purpose

Create a walkable navigation network inside the cemetery boundary. This enables Dijkstra shortest-path routing for visitors on the public "Find a Loved One" page.

### Workflow

#### Add Nodes (Waypoints)

1. Click **draw mode** on the map
2. Click to place nodes at key locations:
   - Gates/entrances
   - Building entrances (chapel, office)
   - Walkway intersections
   - Facilities (restrooms, waiting areas)
   - Grave sections / block corners
3. Each node gets a name, label, lat/lng, and type (entrance, walkway, junction, amenity)

#### Connect Nodes with Edges

1. Switch to **edge mode**
2. Click two nodes to connect them with a walkable path
3. Distance auto-calculates (Haversine formula) in meters
4. Set path type: walkway, road, ramp, or stair
5. Edges can be one-way or bidirectional

#### Test the Path

1. Use the **Find Path** tool
2. Select a start and end node
3. System returns the shortest route with total distance

### What Gets Created

| Table | Record |
|-------|--------|
| `path_nodes` | Each waypoint (lat/lng, name, label, type) |
| `path_edges` | Each connection (from_node → to_node, weight in meters, path_type) |

### Export/Import

- **Export** all nodes + edges as JSON backup
- **Import** a previously exported JSON file
- **Reset** to clear all pathway data

---

## Step 3: Burial Plots

**Route:** `GET /plots`
**Nav:** Map → Burial Plotting & Blocks
**View:** Leaflet satellite map with Leaflet.draw polygon editor + CRUD list

### Purpose

Place individual burial plots within the cemetery boundary. Each plot has a drawn rectangular/polygon shape, lot type, price, and availability status. These plots are what RCC assigns to clients during contract creation.

### Workflow (Index Map)

The `/plots` index page combines the map and list in one view:

1. **View all plots** — existing plots appear as colored polygons on the map:
   - 🟢 **Green** = available
   - 🟡 **Amber** = reserved
   - 🔴 **Red** = occupied / full
2. **Draw a new plot** — click the **Draw a rectangle** button (top-right toolbar), then drag on the map to draw the plot shape
3. **Rotate & scale** — after drawing, `leaflet-path-transform` handles appear at the corners and top-center; drag to rotate, scale, or move the shape to match the exact plot orientation on the satellite view
4. **Confirm shape** — click the **Confirm Shape** button (below the map) to lock in the rotated/scaled coordinates and enable saving
5. **Fill in details** — a modal form slides in with:
   - **Plot Number** — unique identifier (e.g. A-01, B-02)
   - **Section / Block** — area name
   - **Lot Type** — Individual or Family
   - **Dimension** — e.g. 1.5m × 2.5m
   - **Capacity** — max occupants (1 for individual, 2+ for family)
   - **Price** — rental/sale price
   - **Status** — available, reserved
   - **Notes** — optional
4. **Save** — the shape (GeoJSON polygon) is stored in `shape`, centroid auto-calculated into `lat`/`lng`
5. **Click an existing polygon** → popup shows plot info + Edit link
6. **Search** — filter plots by number or section via the sidebar

### Workflow (Create Page)

The dedicated `/plots/create` page offers a split layout: drawing map on the left, form on the right.

1. Use **Leaflet.draw rectangle/polygon** tool on the satellite map (within the cemetery boundary)
2. Once drawn, `leaflet-path-transform` handles appear (corner handles for scale, top-center handle for rotation)
3. Drag the handles to rotate/scale the shape to match the satellite view exactly
4. Click **Confirm Shape** (button below map) to finalize — validates all 4 corners are inside the cemetery boundary, computes centroid via Turf.js, and populates hidden `shape`/`lat`/`lng` fields
5. Fill in remaining fields (plot number, section, lot type, etc.)
6. Submit — stores shape + form data

> **Why `leaflet-path-transform` instead of custom rotation?** The plugin provides a professional drag/rotate/scale UX with proper handle markers. After each transform operation (rotateend, scaleend, dragend), it bakes the transformation into the layer's latlngs, so `layer.getLatLngs()` returns the actual rotated coordinates without manual computation.

### Plot Fields

| Field | Description |
|-------|-------------|
| `plot_number` | Unique ID (e.g. A-01) |
| `section` | Block or section name |
| `shape` | GeoJSON polygon coordinates (stored as JSON) |
| `lat` / `lng` | Centroid of the drawn shape (auto-calculated) |
| `lot_type` | `individual` or `family` |
| `dimension` | Physical dimensions (e.g. 1.5m × 2.5m) |
| `capacity` | Max occupants (1 individual, 2+ family) |
| `current_occupants` | Auto-updated when burials are created |
| `status` | `available` · `reserved` · `occupied` · `full` |
| `price` | Rental/sale price |

### Interactive Map Features

- **Color-coded polygons**: green=available, amber=reserved, red=occupied/full
- **Click a polygon** → fly-to + popup with plot info + Edit link
- **Draw new**: use Leaflet.draw toolbar (rectangle or polygon)
- **Edit existing**: `/plots/{id}/edit` — has editable shape with Leaflet.draw edit controls
- **Fallback markers**: plots without a shape (legacy data) appear as circle markers
- **Search** plots by number or section

### Status Lifecycle

```
available → reserved (when contract created) → occupied (when burial scheduled) → full (when at capacity)
```

---

## End-to-End Engr Flow

```
[ENGINEER]
     │
     ├── 1. CEMETERY POLYGONS
     │     └── Draw boundary → Save → Area calculated
     │
     ├── 2. MAP PATHING
     │     ├── Place nodes (gates, walkways, intersections)
     │     └── Connect edges (walkable paths)
     │
     └── 3. BURIAL PLOTS
           ├── Add individual plots (click map + fill form)
           ├── Set lot type (individual / family)
           ├── Set capacity and dimensions
           └── Set availability status
```

Once the Engr finishes setup, the RCC uses the plots for contract creation and the public uses the pathways for navigation.

---

## Tools, Libraries & Algorithms

### Frontend Libraries

| Library | Usage |
|---------|-------|
| **Leaflet.js** | Open-source interactive map for all 3 steps (boundary drawing, node editing, plot markers). |
| **Leaflet.draw** | Plugin for drawing/editing polygons (Cemetery Polygons), polylines (Paths), and rectangles/polygons (Burial Plots). |
| **leaflet-path-transform** | Drag/rotate/resize handler for vector features — adds corner and rotation handles on drawn shapes (used in Burial Plots). |
| **Google Satellite tiles** | High-resolution satellite imagery used as the base map layer. |
| **Turf.js** | Client-side geospatial analysis: polygon area calculation, point-in-polygon checks, centroid computation, coordinate math. |

### Backend (PHP)

| Component | Usage |
|-----------|-------|
| **Laravel** | Full-stack framework handling CRUD, routing, migrations, and API endpoints. |
| **GeoJSON** | Standard format for storing polygon coordinates in the `cemetery_polygons` table. Stored as a JSON text column. |

### Algorithms

| Algorithm | Where | What It Does |
|-----------|-------|--------------|
| **Haversine formula** | `PathEdge` model (auto-calculated on save) | Computes great-circle distance (meters) between two lat/lng nodes. `dlat = lat2 - lat1; dlon = lon2 - lon1; a = sin²(dlat/2) + cos(lat1)·cos(lat2)·sin²(dlon/2); c = 2·atan2(√a, √(1-a)); d = R·c` (Earth radius R = 6371000m). |
| **Dijkstra's algorithm** | `PathController::findPath` | Finds the shortest route between two nodes on a weighted graph. Implemented as a priority-queue-based search: visit neighbors, relax edges, backtrack once destination is reached. |
| **Shoelace formula (Turf.js)** | Frontend (Cemetery Polygons save) | Calculates polygon area from vertex coordinates. `Area = ½·|Σ(xi·y(i+1) - x(i+1)·yi)|`, executed by Turf.js `area()` function. |

### Data Structures

| Structure | Used For |
|-----------|----------|
| **Adjacency List (graph)** | Path nodes connected by edges — loaded as an adjacency list for Dijkstra traversal. |
| **GeoJSON Polygon** | Cemetery boundary — array of `[lng, lat]` coordinate rings. |
| **Quadkey / Tile Coordinates** | Google Satellite tile URL construction (z/x/y) for map rendering. |

---

## What's Still Missing in the System

### 🔴 User & Access Control
| Missing | Impact |
|---------|--------|
| **User CRUD** — No `UserController` or user management views | Super Admin cannot add/edit/disable staff accounts from UI; must use tinker/seeder |
| **Role CRUD** — No UI for managing roles/permissions | Roles hardcoded in seeder; cannot create custom roles or adjust permissions |
| **Password Reset** — No forgot-password flow for staff | Users locked out must be reset via artisan tinker |
| **Own Profile Edit** — Staff can't update their own name/password | Only email/password change via Breeze profile page, no name/role info |

### 🔴 Reports & Data Export
| Missing | Impact |
|---------|--------|
| **No report module** — No `ReportController` or report views | Cannot generate summary reports (monthly revenue, occupancy trends, expired contracts) |
| **No CSV/XLSX export** — Maatwebsite/Laravel Excel not installed | Cannot export any list (contracts, payments, burials) to spreadsheet |
| **No financial analytics** — No income statements, revenue breakdowns, or aging reports | Management cannot track financial performance beyond dashboard totals |
| **No PDF for AF 51 receipt** — Only contract PDF exists | Cannot print or email the official receipt (AF 51) separately |

### 🔴 Email & Notifications
| Missing | Impact |
|---------|--------|
| **MAIL_MAILER=log** — All emails written to log file, never sent | Clients never receive email notifications (payment received, permit issued, etc.) |
| **No SMTP config** — Uses dummy `hello@example.com` | Even if mailer changed, from-address is wrong |
| **Queue worker not running** — `queue:work` not active | All `ShouldQueue` notifications (InstallmentReminder, PermitIssued, etc.) pile up in jobs table unprocessed |
| **No SMS gateway** — No SMS provider integration | Cannot notify clients without email/smartphone |
| **No real-time alerts** — No Pusher/WebSocket/SSE setup | Dashboard notifications don't appear in real time; requires page refresh |

### 🔴 Automation & Cron
| Missing | Impact |
|---------|--------|
| **Schedule not wired** — Commands exist but `Kernel.php` has no `$schedule` | `reminders:installment` and `reminders:burial` never auto-fire |
| **No contract expiry cron** — No expiration date checker | Expired contracts are never flagged or changed to expired status |
| **No overdue installment escalation** — No 7/14/30 day notices | Overdue accounts not escalated automatically |

### 🟡 Payment & Billing
| Missing | Impact |
|---------|--------|
| **Installments not auto-updated** — Payment recording doesn't update `installment_schedules` | RCC must manually mark each schedule as paid; risk of data inconsistency |
| **No receipt printing** — No AF 51 template for thermal/inkjet printer | Cannot issue official receipts to clients |
| **No payment gateway** — No GCash/Maya/BPI/credit-card integration | All payments must be cash/check in-person |
| **No refund flow** — No cancellation refund logic | Refunding a cancelled contract is a manual database operation |
| **No payment edit/update** — Route uses `except(['edit', 'update'])` | Mistaken entries cannot be corrected; must delete and re-enter |

### 🟡 Contract Lifecycle
| Missing | Impact |
|---------|--------|
| **No cancellation workflow** — `destroy()` deletes the contract directly | No cancellation reason, no plot status rollback on cancel |
| **No renewal flow** — Renewal creates entirely new contract | Manual re-entry of all fields; no continuity from previous contract |
| **No contract amendment** — No change-order/amendment process | Cannot modify lot area or payment terms mid-contract with audit trail |
| **No expiration badge/alert** — Dashboard doesn't flag nearing-expiry contracts | RCC may miss renewals, leading to lapsed leases |

### 🟡 Plot & Mapping
| Missing | Impact |
|---------|--------|
| **No bulk plot creation** — Plots added one-by-one | Adding 100+ plots for a new section is tedious |
| **No plot numbering auto-scheme** — `plot_number` is free-text | Risk of inconsistent naming (A-01 vs A1 vs A.1) |

### 🟡 Public Website
| Missing | Impact |
|--------|--------|
| **No online payment** — Public reserve form creates contract but no payment | Clients cannot pay online after reservation |
| **Path routing not shown publicly** — Dijkstra results not rendered on public map | "Find a Loved One" shows marker but not walking route |
| **No public inquiry tracking** — Clients can't check reservation status | Only in-app/database notifications; no self-service portal |
| **Reserve form omits contract_type/ordinance** — PublicBookingController doesn't set these | Public reservations skip rental period info |
| **Public lots map still uses markers** — Shapes not drawn on public view | Public sees only dots, not the actual plot rectangle shapes |

### 🟢 Activity Logs (Partially Implemented)
| Missing | Impact |
|---------|--------|
| **No event listeners/observers** — Controller exists but logs aren't auto-populated | Activity log is always empty; no audit trail for CRUD operations |

### 🟢 Data Integrity
| Missing | Impact |
|---------|--------|
| **No fulltext index migration** — `burials.deceased_name` uses `MATCH AGAINST` without index | Full-text search will fail on MySQL (no index created) |
| **Client ID not unique** — `id_number` column has no unique constraint | Duplicate client records possible |
| **No soft deletes** — All models use hard `delete()` | Accidental data loss cannot be recovered |

### 🟢 Testing
| Missing | Impact |
|---------|--------|
| **No feature/unit tests** — No tests for controllers, services, or models | No regression safety net for any custom module |
| **No test factories** — Only `UserFactory` exists | Can't easily seed test data for testing |
