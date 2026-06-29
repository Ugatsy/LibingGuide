# Cemetery Management System — System Flow Guide

> A cemetery management system for Solano, Nueva Vizcaya.
> Built with Laravel, MySQL, Leaflet maps, Dijkstra pathfinding.

---

## Table of Contents

1. [System Overview](#1-system-overview)
2. [Navigation](#2-navigation)
3. [Module: Dashboard](#3-module-dashboard)
4. [Module: Plots (Lots)](#4-module-plots-lots)
5. [Module: Clients](#5-module-clients)
6. [Module: Contracts & Billing](#6-module-contracts--billing)
7. [Module: Burial Permits (AF 58)](#7-module-burial-permits-af-58)
8. [Module: Burials](#8-module-burials)
9. [Module: Deceased Registry](#9-module-deceased-registry)
10. [Module: Pre-Need Plans](#10-module-pre-need-plans)
11. [Module: Columbary Niches](#11-module-columbary-niches)
12. [Module: Cemetery Map & Graves](#12-module-cemetery-map--graves)
13. [Module: Pathways (Dijkstra Navigation)](#13-module-pathways-dijkstra-navigation)
14. [Module: Activity Logs](#14-module-activity-logs)
15. [Module: Notifications](#15-module-notifications)
16. [Module: Inquiries](#16-module-inquiries)
17. [Module: Legacy SVG Map](#17-module-legacy-svg-map)
18. [Public-Facing Website](#18-public-facing-website)
19. [End-to-End User Flows](#19-end-to-end-user-flows)
20. [Technical Reference](#20-technical-reference)
21. [Mapping Flow (Engr)](#mapping-flow-engr) — see [`MAPPING_FLOW.md`](MAPPING_FLOW.md)

---

## 1. System Overview

### What This System Does

The system manages the complete lifecycle of cemetery operations:

- **Plot management** — create, map, and track burial lots
- **Client management** — register families/representatives handling deceased paperwork
- **Contracts & billing** — lease agreements with installment or cash payment
- **Burial permits (AF 58)** — official permit issued before interment
- **Burial scheduling** — schedule, approve, and track interments
- **Deceased registry** — searchable directory of all interred persons
- **Pre-need plans** — sell memorial/burial/funeral packages in advance
- **Columbary niches** — manage wall niches for cremated remains
- **Cemetery mapping** — draw cemetery boundaries, seed grave markers
- **Pathway navigation** — Dijkstra-based pathfinding for visitors
- **Public website** — browse lots, plans, search for loved ones
- **Notifications & reminders** — installment due alerts, burial reminders

### Actors / User Roles

| Role | Description | Modules |
|------|-------------|---------|
| **Client / Inquirer** | Subject of the contract. No system account — fills public forms to inquire, reserve lots/plans/niches, or search for loved ones. | Public-Facing Website (Browse Lots, Browse Plans, Reserve, Find a Loved One, Contact/Inquiry) |
| **Staff / RCC** | Handles contract approvals (preparer), overview of sales & all contracts, grave seed availability & renewals, ordinance price changes, report generation. | Dashboard, Clients, Plots (view availability), Contracts & Billing (CRUD + approvals), Burial Permits (AF 58), Burials, Deceased Registry, Pre-Need Plans, Columbary Niches, Notifications, Inquiries, Reports |
| **Engr** | Map plotter, grave plotter, overview of map/capacity. Manages cemetery boundaries, pathways, and plot mapping on the satellite view. See [`MAPPING_FLOW.md`](MAPPING_FLOW.md). | Dashboard (capacity stats), Plots (Lots), Cemetery Map & Graves, Pathways (Dijkstra), Legacy SVG Map |
| **Super Admin** | Creates accounts for all roles, full control panel, can disable any feature/module, views activity logs, manages system settings. | **All modules** (full access), Activity Logs, User/Role Management, Control Panel (feature toggles), System Settings |

> **Note:** Treasurer and Mayor sign physical papers (client carries the paper between offices). RCC verifies both signatures and marks both approvals in the system.

### Workflow at a Glance

```
Client brings Death Certificate → RCC
        ↓
RCC issues AF 58 Burial Permit, checks available grave,
  sets ordinance price, assigns grave number
        ↓
Client pays at Treasurer → gets physical signature
        ↓
Client gets physical signature from Mayor
        ↓
Client returns signed papers to RCC
        ↓
RCC verifies signatures & approves both in system
        ↓
Contract complete → SMS/email reminder every 10 years for renewal
        ↓
Burial scheduled & interment completed
        ↓
Deceased recorded in searchable registry
```

---

## 2. Navigation

### App Navigation Bar (Staff / Engr / Super Admin)

```
[Dashboard] [Clients] [Lots & Burials] [Permits (AF 58)] [Contracts & Billing] [Services ▼] [Inquiries] [Notifications]
```

**Services dropdown:**
- Pre-Need Plans
- Columbary Niches
- Legacy Map
- Cemetery Map
- Pathways

### Public Navigation Bar

```
[Logo] [Home] [About] [Services ▼] [Find a Loved One] [Contact] [Staff Login]
```

**Services dropdown:**
- Memorial Lots
- Pre-Need Plans
- Columbarium (Coming Soon)

---

## 3. Module: Dashboard

**Route:** `GET /dashboard`
**Controller:** `DashboardController@index`
**View:** `dashboard.blade.php`

### Stats Cards

| Card | Description |
|------|-------------|
| **Total Plots** | Count of all plots, with available/occupied breakdown |
| **Occupancy Rate** | Percentage of cemetery occupied |
| **Revenue** | Sum of all payments collected |
| **Upcoming Burials** | Scheduled burials (future date) |
| **New Inquiries** | Unread contact form submissions |
| **Pre-Need Plans** | Count of active plans |
| **Available Niches** | Columbary niches for sale |
| **Burial Permits Issued** | Count of issued AF 58 permits |
| **Pending Approvals** | Contracts awaiting RCC to verify physical signatures & approve |

### Recent Activity

- **Recent Burials** — Last 5 burials with name, plot, date
- **Recent Payments** — Last 5 payments with client name, amount, date

---

## 4. Module: Plots (Lots)

**Routes:** `GET|POST|PUT|DELETE /plots`
**Controller:** `PlotController`
**Model:** `Plot` — `plots` table
**Key View:** `plots/index.blade.php` (interactive Leaflet map)

### Plot Fields

| Field | Type | Description |
|-------|------|-------------|
| `plot_number` | varchar (unique) | e.g. A-01, B-02 |
| `section` | varchar | Block or section name |
| `lat` / `lng` | decimal (10,8)/(11,8) | GPS coordinates for map |
| `capacity` | tinyint | Max occupants (1 for individual, 2+ for family) |
| `current_occupants` | tinyint | How many are currently buried here |
| `status` | enum | `available`, `reserved`, `occupied`, `full` |
| `price` | decimal | Rental/sale price |
| `notes` | text | Optional notes |

### Workflow: Creating a Plot

1. Navigate to **Lots & Burials** in the nav
2. Click **+ Add Plot**
3. Click on the map to place the plot pin (lat/lng auto-filled)
4. Fill in: plot number, section, capacity, price, status, notes
5. Submit

> **Tip:** Plots can also be created from the map in **pin mode** — click the map to drop a pin, then fill the form.

### Interactive Map Features

- **Satellite tile layer** (Google satellite imagery)
- **Color-coded markers**: green=available, amber=reserved, red=occupied/full
- **Click a marker** → fly-to + popup with plot info
- **Drag a marker** to reposition (relocate mode)
- **Right-click** or use sidebar button to delete a plot
- **Search** plots by number or section

---

## 5. Module: Clients

**Routes:** `GET|POST|PUT|DELETE /clients`
**Controller:** `ClientController`
**Model:** `Client` — `clients` table

### Client Fields

| Field | Type | Description |
|-------|------|-------------|
| `full_name` | string | Client/representative name |
| `contact_number` | string | Phone number |
| `email` | string | Optional email |
| `address` | text | Home address |
| `id_number` | string | Government ID number |
| `id_type` | string | ID type (PhilSys, Passport, UMID, etc.) |

### Who Are Clients?

Clients are **the bereaved family members or representatives** who:
- Bring the deceased's death certificate to the office
- Pay for burial permit fees and lot rental
- Sign the Cemetery Lease Contract
- Are responsible for installment payments

### Workflow: Adding a Client

1. Go to **Clients** → **+ Add Client**
2. Enter full name, contact number, email, address
3. Enter ID type and ID number for verification
4. Submit

> **Note:** A client cannot be deleted if they have active contracts.

---

## 6. Module: Contracts & Billing

**Routes:** `GET|POST|PUT|DELETE /contracts`
**Controller:** `ContractController`
**Model:** `Contract` — `contracts` table
**Sibling:** `Payment` — `payments` table
**Sibling:** `InstallmentSchedule` — `installment_schedules` table

### Contract Fields

| Field | Type | Description |
|-------|------|-------------|
| `client_id` | FK → clients | The bereaved family rep |
| `plot_id` | FK → plots | The burial lot (nullable) |
| `pre_need_plan_id` | FK → plans | Optional plan reference |
| `columbary_niche_id` | FK → niches | Optional niche reference |
| `contract_date` | date | Date of contract signing |
| **`lot_type`** | enum | `individual` or `family` |
| **`lot_area`** | decimal | Area in square meters (family lots) |
| **`dimension`** | string | e.g. "2.5m × 5.0m" |
| **`contract_type`** | enum | `new` or `renewal` (10-year lease) |
| **`commencement_date`** | date | Lease start date |
| **`expiration_date`** | date | Lease end date (+10 years) |
| `total_amount` | decimal | Total contract value |
| `payment_type` | enum | `cash`, `credit_card`, `installment` |
| `status` | enum | `active`, `completed`, `cancelled` |
| **`prepared_by`** | FK → users | RCC who prepared it |
| **`approved_by_treasurer_at`** | timestamp | When RCC verified Treasurer's physical signature (set by RCC) |
| **`approved_by_mayor_at`** | timestamp | When RCC verified Mayor's physical signature (set by RCC) |
| **`af_51_number`** | string | Official Receipt number |
| **`af_51_date`** | date | Date of official receipt |
| **`death_certificate_number`** | string | Reference to death cert |

> **Bold fields** = added during the Solano procedure implementation.

### Payment Fields

| Field | Type | Description |
|-------|------|-------------|
| `contract_id` | FK | Parent contract |
| `amount_paid` | decimal | Payment amount |
| `payment_type` | enum | `cash`, `credit_card`, `installment` |
| `reference_number` | string | Bank/transaction reference |
| `receipt_number` | string | Auto-generated receipt number |
| `paid_at` | timestamp | When payment was made |

### Workflow: Creating a Contract

#### Step 1: Client walks in to RCC
Client brings the deceased's death certificate to the RCC office.

#### Step 2: RCC Issues Burial Permit (AF 58)
See [Burial Permits module](#7-module-burial-permits-af-58).

#### Step 3: RCC Checks Available Grave & Sets Ordinance Price
- Checks grave seed availability
- Assigns a **grave number** to the client
- Computes rental fee based on **ordinance period** and **lot type**:

  | Lease Type | Individual Lot | Family Lot |
  |------------|----------------|------------|
  | **New** | ₱2,000 (10 years) | ₱2,000 (10 years) |
  | **Renewal — Before 2002** | ₱20/yr = ₱200/10yrs | Area × yrs × ₱8/sqm/yr |
  | **Renewal — 2002 to 2013** | ₱70/yr = ₱700/10yrs | Area × yrs × ₱28/sqm/yr |
  | **Renewal — 2013 to Present** | ₱200/yr = ₱2,000/10yrs | Area × yrs × ₱80/sqm/yr |

#### Step 4: RCC Creates the Contract

1. Go to **Contracts & Billing** → **+ New Contract**
2. Select **Client**
3. Choose **Contract Type**: Lot / Columbary / Plan
4. For **Lot**:
   - Select the **Plot** from dropdown
   - Choose **Lot Type**: Individual or Family
   - Choose **Lease Type**: New or Renewal
   - If **Renewal**, select the **Ordinance Period** (Before 2002 / 2002–2013 / 2013–Present)
   - Enter **Lot Area** (sqm) for family lots
   - Enter **Dimension** (e.g. "2.5m × 5.0m")
   - Enter **Commencement Date** and **Expiration Date**
5. **Total Amount** auto-computed from ordinance rates (click **Apply to Total Amount** to confirm)
6. Choose **Payment Type** (cash / credit card / installment)
7. Fill **AF 51 / Official Receipt** details
8. Enter **Death Certificate Number**
9. Submit

#### Step 5: Client Pays at Treasurer

Client proceeds to the **Treasurer's office**, pays the fee, and receives the **physical signed paper** from the Treasurer.

#### Step 6: Client Gets Mayor's Signature

Client proceeds to the **Mayor's office** and receives the **physical signed paper** from the Mayor.

#### Step 7: Client Returns to RCC

Client brings back both physically signed papers (Treasurer + Mayor) to the RCC.

#### Step 8: RCC Verifies & Approves in System

RCC verifies both physical signatures are present, then marks both approvals in the system:

- **Approve Treasurer** → sets `approved_by_treasurer_at`
- **Approve Mayor** → sets `approved_by_mayor_at`

The contract show page shows green checks for each approval step.

#### Step 9: Contract Complete — 10-Year Renewal Reminder

Contract is now active. The system schedules an **SMS/email notification** to the client's contact number/email **every 10 years** when the lease is due for renewal.

#### Step 10: Installments (if applicable)

If payment type is **Installment**, the system auto-generates monthly schedules:
- Equal monthly payments
- Due dates: 1st of each month starting from creation
- Tracked in the installments table on contract show page

#### Step 11: Download PDF Contract

Click **Download PDF** on the contract show page to generate a DomPDF document with:
- Client information
- Service details (plot, plan, or niche)
- Installment schedule
- Payment history
- Signature lines

---

## 7. Module: Burial Permits (AF 58)

**Routes:** `GET|POST|PUT|DELETE /burial-permits`
**Controller:** `BurialPermitController`
**Model:** `BurialPermit` — `burial_permits` table

### Why AF 58?

AF 58 is the official **Burial Permit** — a legal document required before interring a deceased person in the municipal cemetery. It serves as proof that:
- The death certificate has been verified
- Burial permit fees have been paid
- The interment is authorized by the LGU

### Permit Fields

| Field | Description |
|-------|-------------|
| `permit_number` | Auto-generated (AF58-XXXXXX) |
| `deceased_name` | Full name of the deceased |
| `date_of_birth` | Optional DOB |
| `date_of_death` | Date of passing |
| `death_certificate_number` | Reference to death certificate |
| `burial_permit_fee` | Fee collected (default ₱200) |
| `issued_by` | Staff who issued the permit |
| `issued_at` | Timestamp of issuance |
| `status` | `issued`, `used`, `cancelled` |

### Workflow: Issuing a Burial Permit

1. Client submits **Death Certificate** with proper signatures
2. Staff goes to **Permits (AF 58)** → **+ Issue Burial Permit**
3. Select the **Contract** (must exist first)
4. Fill:
   - Deceased name, DOB, DOD
   - Death certificate number
   - Burial permit fee (₱200.00 default)
   - Notes (optional)
5. Submit → Permit number auto-generated (AF58-000001, etc.)
6. **Original copy** issued to client, duplicate retained for records

### Permit Statuses

- **Issued** — Freshly printed, client has the original
- **Used** — Burial has been completed (updated by staff)
- **Cancelled** — Voided for any reason

### Rental Fee Computation (API)

**`POST /burial-permits/compute-rental`**

Parameters:
- `contract_type` — `new` or `renewal`
- `ordinance_period` — `pre_2002`, `2002_2013`, or `2013_present` (required for renewal)
- `lot_type` — `individual` or `family`
- `area` — sqm (required for family lots)

Returns (new):
```json
{
  "type": "new",
  "fee": 2000,
  "years": 10,
  "breakdown": "New lot fee: ₱2,000.00"
}
```

Returns (renewal):
```json
{
  "type": "renewal",
  "ordinance_period": "2013_present",
  "fee": 2000,
  "years": 10,
  "annual": 200,
  "annual_rate": 200,
  "breakdown": "₱200/yr × 10 yrs = ₱2,000.00"
}
```

---

## 8. Module: Burials

**Routes:** `GET|POST|PUT|DELETE /burials`
**Controller:** `BurialController`
**Model:** `Burial` — `burials` table

### Burial Fields

| Field | Description |
|-------|-------------|
| `plot_id` | FK → plots (where interred) |
| `contract_id` | FK → contracts (financial agreement) |
| `deceased_name` | Full name (FULLTEXT indexed for search) |
| `date_of_birth` | Optional |
| `date_of_death` | Date of passing |
| `burial_date` | Date/time of interment |
| `burial_status` | `scheduled`, `completed`, `cancelled` |
| `scheduled_by` | FK → users (staff who scheduled) |
| `approved_at` | Timestamp of approval |
| `notes` | Optional notes |

### Workflow: Scheduling a Burial

#### Prerequisites
- A **Contract** must exist for the plot
- The plot must have available capacity (occupants < capacity)

#### Steps

1. Go to **Lots & Burials** → the burial list shows under the main burials tab
2. Click **+ Schedule Burial**
3. Select:
   - **Deceased Name**
   - **Plot** (only shows plots with available capacity)
   - **Contract** (only active contracts)
   - **Date of Birth** (optional)
   - **Date of Death**
   - **Burial Date & Time**
   - **Status** (default: Scheduled)
   - **Notes** (optional)
4. Submit

#### Auto-Updates on Plot

When a burial is created:
- `plot.current_occupants` increments by 1
- Plot status auto-updates:
  - If occupants ≥ capacity → `full`
  - If plot was `available` → `occupied`

#### Approving a Burial

1. From the burials list, click **Approve** on a scheduled burial
2. Sets `burial_status = completed` and `approved_at = now()`

#### Deleting a Burial

- Decrements `plot.current_occupants`
- Restores `occupied` status if occupants < capacity

---

## 9. Module: Deceased Registry

**Route:** `GET /deceased`
**Controller:** `DeceasedController@index`
**View:** `deceased/index.blade.php`

### What It Shows

A unified, searchable listing of all deceased persons from two sources:

| Source | Records Included |
|--------|-----------------|
| **Burials** | All completed burials |
| **Burial Permits (AF 58)** | All non-cancelled permits |

### Displayed Information

| Column | Description |
|--------|-------------|
| Deceased Name | Full name |
| Date of Birth | Optional |
| Date of Death | Sorted descending by this |
| Plot | Plot number + section |
| Client | Bereaved family rep |
| Source | "Burial Record" or "Burial Permit (AF 58)" |

### Search

Real-time client-side search by deceased name.

---

## 10. Module: Pre-Need Plans

**Routes:** `GET|POST|PUT|DELETE /pre-need-plans`
**Controller:** `PreNeedPlanController`
**Model:** `PreNeedPlan` — `pre_need_plans` table

### What Are Pre-Need Plans?

Pre-need plans allow families to purchase burial/memorial packages **in advance**, locking in current prices and securing a lot for future use.

### Plan Types

| Type | Description |
|------|-------------|
| **Memorial** | Garden lot or columbary niche with perpetual care |
| **Burial** | Family estate lot for multiple occupants |
| **Funeral** | Complete funeral service package |

### Seeded Plans

| Plan | Type | Price | Features |
|------|------|-------|----------|
| Garden Memorial | Memorial | ₱45,000 | Garden lot, perpetual care, annual memorial, online page, security |
| Family Legacy | Burial | ₱120,000 | Family estate (4 occupants), granite marker, perpetual care, private viewing |
| Columbary Peace | Memorial | ₱25,000 | Niche, brass plate, perpetual care, remembrance service |
| Funeral Service | Funeral | ₱85,000 | Chapel (3 days), hearse, casket, embalming, coordination |

### Workflow

1. Go to **Services** → **Pre-Need Plans**
2. Click **+ Add Plan**
3. Fill: name, type, price, description, features (one per line), image URL
4. Toggle active/inactive
5. Submit

> Plans with active contracts cannot be deleted.

---

## 11. Module: Columbary Niches

**Routes:** `GET|POST|PUT|DELETE /columbary-niches`
**Controller:** `ColumbaryNicheController`
**Model:** `ColumbaryNiche` — `columbary_niches` table

### What Is a Columbary?

A columbary is a structure with **niches** (wall compartments) for storing cremated remains (urns).

### Niche Fields

| Field | Description |
|-------|-------------|
| `niche_number` | Unique identifier (e.g. CN-A01) |
| `section` | Garden/wing section |
| `row` | Row number |
| `tier` | Tier level (1=bottom, 2=middle, 3=top) |
| `status` | `available`, `reserved`, `occupied` |
| `price` | Sale price |
| `map_x` / `map_y` | Position on map (for future interactive view) |

### Workflow

1. Go to **Services** → **Columbary Niches**
2. Add/Edit/Delete niches
3. Niches can be reserved through contracts (lot/columbary/plan selector)
4. Available niches shown in dashboard stats

---

## 12. Module: Cemetery Map & Graves

**Routes:**
- `GET /cemetery/admin` — Main admin interface
- `POST /cemetery/polygon` — Save cemetery boundary
- `POST /cemetery/graves` — Add individual grave marker
- `GET /cemetery/graves` — Get all graves as GeoJSON
- `POST /cemetery/import` — Import GeoJSON file
- `GET /cemetery/seed` — Auto-generate sample graves
- `GET /cemetery/find-path` — Public pathfinding endpoint

**Controller:** `CemeteryMapController`
**Models:** `CemeteryPolygon`, `Grave` — `cemetery_polygons`, `graves` tables

### Purpose

This module manages the **physical cemetery grounds** — drawing the cemetery boundary and placing individual grave markers on the satellite map.

### Workflow: Creating the Cemetery Map

#### Step 1: Draw the Cemetery Boundary

1. Go to **Services** → **Cemetery Map**
2. Click vertices on the satellite map to outline the cemetery
3. The polygon shows the cemetery boundary
4. Area is automatically calculated (sqm + hectares) using Turf.js
5. Click **Save Polygon** to store it

#### Step 2: Add Grave Markers (Individual or Batch)

**Individual grave:**
1. Use the **Add Grave Marker** button
2. Enter: full name, birth/death dates, section, plot number
3. Click on the map to place the marker

**Batch grave seeding:**
1. Click **Seed Sample Graves** to auto-generate 15 random graves inside the polygon boundary
2. Each grave gets random coordinates within the boundary using ray-casting point-in-polygon

**Import from GeoJSON:**
1. Click **Import GeoJSON**
2. Upload a valid GeoJSON file with polygon and/or point features

### Grave Fields

| Field | Description |
|-------|-------------|
| `full_name` | Name of the deceased |
| `birth_date` | Date of birth |
| `death_date` | Date of death |
| `section` | Cemetery section |
| `plot_number` | Plot identifier |
| `latitude` / `longitude` | GPS coordinates |
| `description` | Additional info |
| `image_url` | Optional photo |

### Public Pathfinding (Find Path to Grave)

**`GET /cemetery/find-path?start_lat=...&start_lng=...&end_lat=...&end_lng=...`**

1. Takes starting coordinates (visitor's current location) and ending coordinates (grave/target)
2. Finds the nearest **path nodes** to both points
3. Runs **Dijkstra's shortest path algorithm** on the pathway network
4. Returns a GeoJSON LineString with visual instructions

---

## 13. Module: Pathways (Dijkstra Navigation)

**Routes:** `GET|POST|DELETE /paths/*`
**Controller:** `PathController`
**Models:** `PathNode`, `PathEdge`
**Services:** `DijkstraService`, `PathManagerService`

### Purpose

Creates a **navigation graph** of walkways and roads inside the cemetery, enabling:
- Shortest path routing between any two points
- Visual navigation on the public find-a-loved-one map
- Waypoint management (entrances, facilities, chapels, etc.)

### Data Structure

```
PathNode (vertices)                    PathEdge (connections)
┌─────────────────────┐               ┌─────────────────────┐
│ id                  │               │ id                  │
│ name (e.g. "Gate")  │               │ from_node_id ───────┤
│ label (e.g. "Main") │               │ to_node_id ─────────┤
│ lat                 │               │ weight (meters)     │
│ lng                 │               │ path_type           │
│ type (waypoint)     │               │ is_bidirectional    │
└─────────────────────┘               └─────────────────────┘
```

### Workflow: Building Pathways

#### Step 1: Add Nodes (Waypoints)

1. Go to **Services** → **Pathways**
2. Click **draw mode** on the map
3. Click to place nodes at key locations:
   - Gates/entrances
   - Building entrances (chapel, office)
   - Intersections/crossroads
   - Facilities (comfort rooms, waiting areas)
   - Grave sections

#### Step 2: Connect Nodes with Edges

1. In **edge mode**, click two nodes to connect them
2. Weight auto-calculates using Haversine distance (actual walkable meters)
3. Edges can be one-way or bidirectional
4. Path types: `walkway`, `road`, `ramp`, `stair`

#### Step 3: Test Pathfinding

1. Use the **Find Path** tool
2. Select start and end nodes
3. System returns:
   - Ordered list of nodes to traverse
   - Total distance in meters
   - Visual path on the map

### Dijkstra Algorithm Details

The `DijkstraService` implements:
1. **Adjacency list** built from all PathEdges (with bidirectional support)
2. **Haversine formula** for distance calculation between lat/lng pairs
3. **Linear scan** for minimum distance node selection
4. Returns: `path` (ordered node collection), `nodeIds` (array), `distance` (total km)

### Export/Import

- **Export** all nodes and edges as JSON
- **Import** a previously exported JSON backup
- **Reset** to clear all pathway data

---

## 14. Module: Activity Logs

**Route:** `GET /activity-logs`
**Controller:** `ActivityLogController`
**Model:** `ActivityLog` — `activity_logs` table

### What Is Logged

| Event Type | Trigger |
|------------|---------|
| `plot` | Plot created, updated, or deleted |
| `burial` | Burial created, updated, or deleted |
| `payment` | Payment created or deleted |
| `contract` | Contract created, updated, or deleted |

### Log Fields

| Field | Description |
|-------|-------------|
| `user_id` | Staff who performed the action |
| `type` | Event category (burial, payment, contract, plot) |
| `description` | Human-readable description |
| `subject_type` / `subject_id` | Polymorphic reference to affected record |
| `properties` | JSON snapshot of changes |

### Viewing

- Paginated timeline view at `/activity-logs`
- Color-coded dots per event type
- Shows who did what and when

---

## 15. Module: Notifications

**Routes:** `GET /notifications`
**Controller:** `NotificationController`
**Model:** `Notification` — `notifications` table

### Notification Types

| Type | Trigger |
|------|---------|
| `burial_reminder` | Burial scheduled for tomorrow (daily cron) |
| `installment_due` | Installment due in 3 days (daily cron) |
| `overdue` | Installment past due |
| `contract_renewal` | Lease expiring in 30 days (10-year renewal) |
| `system` | General system notifications |

### Automated Reminders

Three artisan commands run daily (via cron):

```
php artisan reminders:burial
— Notifies staff of burials scheduled for tomorrow

php artisan reminders:installment
— Notifies staff of payments due in 3 days

php artisan reminders:contract-renewal
— Sends SMS/email to client when lease is due for renewal (every 10 years)
```

### Viewing

- List of notifications with type badges
- **Mark as Read** (individual)
- **Mark All as Read** (bulk)
- "Unread notifications" count shown in dashboard

---

## 16. Module: Inquiries

**Routes:** `GET|POST|PUT|DELETE /inquiries`
**Controller:** `InquiryController`
**Model:** `Inquiry` — `inquiries` table

### Sources

1. **Public website** — Contact form on the welcome page
2. **Admin Panel** — Staff can manually create inquiries

### Inquiry Fields

| Field | Description |
|-------|-------------|
| `full_name` | Person making the inquiry |
| `contact_number` | Phone number |
| `email` | Email address |
| `address` | Home address |
| `lot_interest` | Type of lot interested in |
| `message` | Their question/message |
| `status` | `new`, `contacted`, `closed` |

### Public Inquiry Flow

1. Visitor fills the contact form on the website
2. Form submits to `POST /inquire`
3. Inquiry appears in admin panel with status `new`
4. Staff views and responds → updates status to `contacted`

---

## 17. Module: Legacy SVG Map

**Routes:** `GET|POST|PUT|DELETE /burial-spots`
**Controller:** `BurialSpotController`
**Model:** `BurialSpot` — `burial_spots` table

### What It Is

A legacy SVG-based map system (520×380 pixel canvas) used before the Leaflet interactive map was built. Still available for reference or as a simpler alternative.

### Features

- SVG viewbox (0-520, 0-380)
- Click to place/position burial spots
- Drag to reposition spots
- Color-coded by status: green=available, red=occupied, etc.
- Modal form for adding spot details

### Fields

| Field | Description |
|-------|-------------|
| `name` | Deceased name |
| `plot_number` | Unique plot ID |
| `section` | Section/block |
| `birth_year` / `death_year` | Years (not full dates) |
| `status` | `occupied`, `reserved`, `available` |
| `map_x` / `map_y` | Position on SVG canvas |

---

## 18. Public-Facing Website

The system includes a complete public-facing website accessible at `/` without authentication.

### Pages

#### Home (`/`)

Landing page with:
- Hero section with tagline: *"Preserving Memories, Honoring Lives"*
- About Us section
- Memorial Lot cards (3 package options)
- Columbarium "Coming Soon" section
- Contact/Inquiry form (with radio toggle: Lot / Plan / Niche / Inquiry)
- Careers / "We Are Hiring" section

#### Memorial Lots (`/lots`)

Interactive satellite map showing available lots:
- **Green markers** = available for reservation
- **Yellow markers** = reserved (pending payment)
- **Red markers** = occupied/full
- Click a lot in the sidebar → map flies to its location
- "Reserve a Lot" button → goes to reservation form

#### Pre-Need Plans (`/plans`)

Cards grouped by type (Memorial, Burial, Funeral):
- Plan name, image/placeholder, price
- Feature list (shows first 4 + "Show all X features" badge)
- "View Details" button → full plan page
- "Apply for This Plan" → pre-filled reservation form

#### Pre-Need Plan Detail (`/plans/{plan}`)

Full details of a single plan:
- Type badge, description
- Complete feature list
- Price display
- "Apply for This Plan" button with pre-selected plan

#### Columbarium (`/columbarium`)

Coming soon page.

#### Reserve a Lot / Plan / Niche (`/reserve/{type}`)

Multi-purpose reservation form:
- **Interactive Leaflet map** for lot selection (clicks synced with dropdown)
- **Dropdown selectors** for niches or plans
- Personal details: name, contact, email, address
- Additional message field
- Submits to `POST /reserve`

**On submit:**
1. Client is found or created (by contact number)
2. Contract is created with `installment` payment type
3. Plot/niche status updated to `reserved`
4. Redirect to confirmation page

#### Find a Loved One (`/find`)

Full-screen search and navigation tool:
- **GPS detection** — finds visitor's current location
- **Boundary validation** — checks if visitor is inside/outside cemetery
- **Search** — full-text search on deceased name (MySQL MATCH AGAINST)
- **Autocomplete** — search suggestions dropdown
- **Results** — markers on map with fly-to
- **Detail panel** — shows deceased info when marker clicked
- **Show Path** — Dijkstra-based turn-by-turn navigation from visitor's location to grave
- **Session state** — persists search across page reloads

**API endpoints for find page:**
- `GET /find/search?q=...` — JSON search results
- `GET /find/markers` — GeoJSON of all completed burials
- `GET /cemetery/find-path?start_lat=...&start_lng=...&end_lat=...&end_lng=...` — Dijkstra path

#### Reservation Confirmed (`/reservation-confirmed`)

Simple success message after submitting a reservation.

---

## 19. End-to-End User Flows

### Flow A: Standard Burial (Walk-in Client)

```
[CLIENT]                         [RCC]                          [SYSTEM]
────────                         ────                           ──────
Brings Death Certificate         
       │                              
       └────────► 1. Verify death cert
                  2. Issue AF 58 Burial      ◄── Creates BurialPermit
                     Permit (collect ₱200)       (auto-gen permit #)
                  3. Check grave availability
                     Set ordinance price
                     Assign grave number
                  4. Create Contract          ◄── Contract created with
                     (RCC prepares)               lot_type, total_amount,
                                                   grave_number, etc.
                  5. Client signs contract     
       ◄────────                              (RCC marks contract prepared)

Client → Treasurer
────────────────────
  Pays fee → gets physical signed paper

Client → Mayor
────────────────
  Gets physical signed paper

Client → Back to RCC
──────────────────────
       └────────► 6. RCC verifies signatures ◄── approved_by_treasurer_at
                      Approves both in system    approved_by_mayor_at
       ◄──────── 7. Release original copy
                  8. Schedule burial          ◄── Burial created, plot updated
                  9. Approve burial           ◄── burial_status=completed
                  10. Record in registry      ◄── Shows in Deceased Registry
                                               
                  [10-YEAR RENEWAL]
                  System sends SMS/email ◄── reminders:contract-renewal
                  to client when lease expires
```

### Flow B: Online Reservation (Website Visitor)

```
[VISITOR]                          [SYSTEM]                       [STAFF]
────────                          ──────                         ──────
Browses lots (/lots)
  │
Browses plans (/plans)
  │
Fills reservation form (/reserve)
  │
  └────────► Client firstOrCreate
             Contract created (installment)
             Plot/niche → reserved
             
Show confirmation                    Staff reviews in Contracts
  │                                      │
  │                                      └── Contacts visitor
  │                                          Completes payment
  │                                          Finalizes contract
  │                                          Schedules burial
```

### Flow C: Visitor Finding a Loved One

```
[VISITOR]                          [SYSTEM]
────────                          ──────
Opens /find
  │
GPS location detected
  │
Types deceased name
  │
  └────────► Full-text search on burials.deceased_name
             │
       ◄──── Returns matching results + map markers
  │
Clicks a result
  │
  └────────► Map flies to grave location
             Shows deceased detail panel
  │
Clicks "Show Path"
  │
  └────────► GET /cemetery/find-path
             Finds nearest PathNodes to visitor + grave
             Runs Dijkstra shortest path
       ◄──── Returns GeoJSON path on map
  │
Visitor follows path to grave
```

### Flow D: Engr Setting Up Cemetery

```
[ADMIN]                           [SYSTEM]
─────                             ──────
1. Create Plots
   │
   ├── Manual: /plots/create (click on map)
   ├── Import: GeoJSON upload
   └── Auto-detect: php artisan plots:detect-from-tiles
   
2. Draw Cemetery Boundary
   │
   └── /cemetery/admin → click polygon vertices → Save

3. Add Grave Markers
   │
   ├── Manual: Add Grave → click on map
   ├── Seed: Generate 15 random graves inside polygon
   └── Import: GeoJSON file

4. Build Pathways
   │
   └── /paths → add nodes + edges → test pathfinding

5. Verify Public Search
   │
   └── /find → search for seeded grave → path works
```

---

## 20. Technical Reference

### Technology Stack

| Component | Technology |
|-----------|------------|
| Framework | Laravel 13.x |
| PHP | 8.5+ |
| Database | MySQL (InnoDB) |
| Frontend | Blade, Alpine.js, Tailwind CSS |
| Maps | Leaflet.js + Google Satellite tiles |
| PDF | DomPDF (Barryvdh) |
| Pathfinding | Custom Dijkstra (Haversine distance) |
| Auth | Laravel Breeze (with role field) |
| Queue/Jobs | Database queue |
| Scheduler | Cron (daily reminders) |

### Key Tables & Relationships

```
users ───┬── burials (scheduled_by)
          ├── burial_permits (issued_by)
          ├── contracts (prepared_by)
          ├── activity_logs
          └── notifications

clients ─── contracts ───┬── payments
                         ├── installment_schedules
                         ├── burials
                         └── burial_permits

plots ───┬── contracts
          └── burials

path_nodes ──┬── path_edges (from_node)
             └── path_edges (to_node)

pre_need_plans ─── contracts
columbary_niches ─── contracts
```

### Artisan Commands

| Command | Description | Schedule |
|---------|-------------|----------|
| `php artisan reminders:burial` | Notify staff of tomorrow's burials | Daily |
| `php artisan reminders:installment` | Notify staff of upcoming installment due dates | Daily |
| `php artisan reminders:contract-renewal` | Send SMS/email to client when lease is due for renewal | Daily |
| `php artisan plots:detect-from-tiles` | Auto-detect graves from satellite tile imagery | Manual |

### API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/find/search?q=` | Search deceased by name (public) |
| GET | `/find/markers` | GeoJSON of completed burials |
| GET | `/cemetery/graves` | GeoJSON of all graves |
| GET | `/cemetery/polygon` | Cemetery boundary polygon |
| GET | `/cemetery/find-path` | Dijkstra path between two points |
| POST | `/burial-permits/compute-rental` | Calculate rental fee |
| GET | `/paths/find` | Find shortest path between nodes |
| GET | `/api/plots` | All plots (Sanctum API) |
| PATCH | `/api/plots/{plot}/position` | Update plot position |

### Seed Data

Run: `php artisan db:seed`

Creates:
- 1 admin user (test@example.com)
- 3 clients (Juan Dela Cruz, Maria Clara, Jose Rizal)
- 8 plots (A-01, A-02, B-01, B-02, C-01, A-004, B-012, 123)
- 4 contracts (2 original + 2 with full fields: family lot, individual lot)
- 4 burials (Maria Santos, Jose Reyes, Maria Leonor x2)
- 6 burial permits (4 used + 2 issued)
- 4 pre-need plans
- 6 columbary niches
- 2 payments
- 1 burial spot
