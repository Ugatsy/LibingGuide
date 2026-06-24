# Memorial Map — Offline Map & Full System Plan

> Laravel 11 · MySQL 8.0+ · Leaflet.js (Offline) · Total Cost: ₱0

---

## Section 1 — Offline Map Strategy

### Why offline?
Cemeteries often have poor or no internet. Tiles are downloaded once, stored in
`public/tiles/`, and served directly from Laravel. The browser never makes an
external map request.

### Step 1 — Get cemetery boundary coordinates
1. Open **openstreetmap.org**
2. Search for the cemetery (e.g. "Baliuag Municipal Cemetery")
3. Right-click the **northwest corner** → note coordinates
4. Right-click the **southeast corner** → note coordinates
5. These two pairs become your `maxBounds` in Leaflet

### Step 2 — Download tiles for free using MOBAC
1. Download MOBAC free from **mobac.sourceforge.io**
2. Select tile source: `ESRI World Imagery` (satellite) or `OpenStreetMap`
3. Draw a rectangle over the cemetery
4. Set zoom levels: **17 to 20**
5. Click **Create Atlas** → format: `Files only (one file per tile)`
6. Copy output folder to `public/tiles/`

> Zoom level guide:
> - 17 = whole cemetery visible
> - 18 = sections visible
> - 19 = individual plots visible
> - 20 = maximum detail
> Size estimate for small cemetery: ~50–200 MB

### Step 3 — Configure Leaflet in Blade

```javascript
// resources/views/burial/map.blade.php

const map = L.map('map', {
    center: [14.9544, 120.9006],   // cemetery center
    zoom: 18,
    minZoom: 17,
    maxZoom: 20,
    maxBounds: L.latLngBounds(
        [14.9525, 120.8985],       // SW corner
        [14.9565, 120.9025]        // NE corner
    ),
    maxBoundsViscosity: 1.0        // hard lock — no panning outside
});

// Local tile files — no internet needed
L.tileLayer('/tiles/{z}/{x}/{y}.png', {
    attribution: 'Tiles © OpenStreetMap / ESRI',
    maxZoom: 20,
    maxNativeZoom: 20,
    errorTileUrl: '/tiles/placeholder.png'
}).addTo(map);
```

### Step 4 — Draggable markers with DB save

```javascript
// On page load, render all plots from the DB
fetch('/api/plots')
  .then(res => res.json())
  .then(plots => {
    plots.forEach(plot => {
      const marker = L.marker([plot.lat, plot.lng], { draggable: true })
        .addTo(map)
        .bindPopup(`<b>${plot.plot_number}</b><br>${plot.deceased_name ?? 'Available'}`);

      marker.on('dragend', function(e) {
        const { lat, lng } = e.target.getLatLng();
        fetch(`/api/plots/${plot.id}/position`, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          body: JSON.stringify({ lat, lng })
        });
      });
    });
  });
```

### Step 5 — Public relative finder map

```javascript
// After search returns a result, fly to the plot
map.flyTo([result.lat, result.lng], 20, { duration: 1.5 });

// Pulse ring to make it easy to spot
L.circle([result.lat, result.lng], {
    color: '#1d9e75',
    radius: 2,
    fillOpacity: 0.4
}).addTo(map);
```

---

## Section 2 — MySQL Database

```sql
CREATE DATABASE memorial_map
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
```

### Table: plots

| Column             | Type                  | Notes                                      |
|--------------------|-----------------------|--------------------------------------------|
| id                 | BIGINT UNSIGNED PK    | Auto increment                             |
| plot_number        | VARCHAR(50) UNIQUE    | e.g. A-01                                  |
| section            | VARCHAR(100)          | e.g. Block 1, Row A                        |
| lat                | DECIMAL(10,8)         | GPS latitude for Leaflet                   |
| lng                | DECIMAL(11,8)         | GPS longitude for Leaflet                  |
| capacity           | TINYINT UNSIGNED      | Max bodies allowed (default 1)             |
| current_occupants  | TINYINT UNSIGNED      | Current interred count (default 0)         |
| status             | ENUM                  | available / reserved / occupied / full     |
| price              | DECIMAL(10,2)         | Plot price in Philippine Peso              |
| notes              | TEXT                  | Admin notes (nullable)                     |
| created_at         | TIMESTAMP             | Laravel managed                            |
| updated_at         | TIMESTAMP             | Laravel managed                            |

### Table: clients

| Column          | Type               | Notes                          |
|-----------------|--------------------|--------------------------------|
| id              | BIGINT UNSIGNED PK |                                |
| full_name       | VARCHAR(255)       |                                |
| contact_number  | VARCHAR(20)        |                                |
| email           | VARCHAR(255)       | nullable                       |
| address         | TEXT               |                                |
| id_number       | VARCHAR(100)       | Government ID number           |
| id_type         | VARCHAR(50)        | e.g. PhilSys, Passport, UMID   |

### Table: contracts

| Column         | Type               | Notes                                      |
|----------------|--------------------|--------------------------------------------|
| id             | BIGINT UNSIGNED PK |                                            |
| client_id      | FK → clients.id    |                                            |
| plot_id        | FK → plots.id      |                                            |
| contract_date  | DATE               |                                            |
| total_amount   | DECIMAL(10,2)      |                                            |
| payment_type   | ENUM               | cash / credit_card / installment           |
| status         | ENUM               | active / completed / cancelled             |
| pdf_path       | VARCHAR(255)       | Path in storage/contracts/                 |

### Table: payments

| Column           | Type               | Notes                        |
|------------------|--------------------|------------------------------|
| id               | BIGINT UNSIGNED PK |                              |
| contract_id      | FK → contracts.id  |                              |
| amount_paid      | DECIMAL(10,2)      |                              |
| payment_type     | ENUM               | cash / credit_card / install |
| reference_number | VARCHAR(100)       | Bank ref or OR number        |
| receipt_number   | VARCHAR(50)        | Auto-generated               |
| paid_at          | TIMESTAMP          |                              |

### Table: installment_schedules

| Column       | Type               | Notes                                 |
|--------------|--------------------|---------------------------------------|
| id           | BIGINT UNSIGNED PK |                                       |
| contract_id  | FK → contracts.id  |                                       |
| due_date     | DATE               | Monthly due date                      |
| amount_due   | DECIMAL(10,2)      |                                       |
| amount_paid  | DECIMAL(10,2)      | default 0                             |
| status       | ENUM               | unpaid / paid / overdue / partial     |
| paid_at      | TIMESTAMP          | nullable                              |

### Table: burials

| Column          | Type               | Notes                                         |
|-----------------|--------------------|-----------------------------------------------|
| id              | BIGINT UNSIGNED PK |                                               |
| plot_id         | FK → plots.id      |                                               |
| contract_id     | FK → contracts.id  |                                               |
| deceased_name   | VARCHAR(255)       | **FULLTEXT indexed** — used for search        |
| date_of_birth   | DATE               | nullable                                      |
| date_of_death   | DATE               |                                               |
| burial_date     | DATETIME           | Scheduled burial date and time                |
| burial_status   | ENUM               | scheduled / completed / cancelled             |
| scheduled_by    | FK → users.id      |                                               |
| approved_at     | TIMESTAMP          | nullable                                      |
| notes           | TEXT               | nullable                                      |

```sql
-- Add FULLTEXT index for fast public name search
ALTER TABLE burials
  ADD FULLTEXT INDEX ft_deceased_name (deceased_name);
```

### Table: activity_logs

| Column        | Type               | Notes                                               |
|---------------|--------------------|-----------------------------------------------------|
| id            | BIGINT UNSIGNED PK |                                                     |
| user_id       | FK → users.id      | nullable (public actions have no user)              |
| type          | ENUM               | burial / payment / contract / plot / system / alert |
| description   | TEXT               | Human-readable log message                          |
| subject_type  | VARCHAR(100)       | Polymorphic model class name                        |
| subject_id    | BIGINT UNSIGNED    | Polymorphic related record ID                       |
| properties    | JSON               | Optional old/new value diff                         |
| created_at    | TIMESTAMP          |                                                     |

### Table: notifications

| Column        | Type               | Notes                                              |
|---------------|--------------------|----------------------------------------------------|
| id            | BIGINT UNSIGNED PK |                                                    |
| user_id       | FK → users.id      |                                                    |
| title         | VARCHAR(255)       |                                                    |
| body          | TEXT               |                                                    |
| type          | ENUM               | burial_reminder / installment_due / overdue / sys  |
| is_read       | TINYINT(1)         | 0 = unread, 1 = read                               |
| link          | VARCHAR(255)       | URL to navigate on click (nullable)                |
| scheduled_at  | TIMESTAMP          | When to fire (nullable)                            |

---

## Section 3 — Laravel Migration Highlights

```php
// plots migration
$table->decimal('lat', 10, 8)->default(0);
$table->decimal('lng', 11, 8)->default(0);
$table->tinyInteger('capacity')->unsigned()->default(1);
$table->tinyInteger('current_occupants')->unsigned()->default(0);
$table->enum('status', ['available','reserved','occupied','full'])->default('available');

// PATCH position route
Route::patch('plots/{plot}/position', [PlotController::class, 'updatePosition'])
     ->name('plots.position');

// updatePosition method
public function updatePosition(Request $request, Plot $plot) {
    $request->validate([
        'lat' => 'required|numeric|between:-90,90',
        'lng' => 'required|numeric|between:-180,180',
    ]);
    $plot->update(['lat' => $request->lat, 'lng' => $request->lng]);
    return response()->json(['success' => true]);
}
```

---

## Section 4 — Notification & Scheduler

```php
// app/Console/Commands/SendBurialReminders.php
// Runs daily at 8AM via scheduler

protected function schedule(Schedule $schedule) {
    $schedule->command('reminders:burial')->dailyAt('08:00');
    $schedule->command('reminders:installment')->dailyAt('08:00');
}

// Burial reminder logic
$tomorrow = Carbon::tomorrow();
Burial::whereDate('burial_date', $tomorrow)
    ->where('burial_status', 'scheduled')
    ->each(function ($burial) {
        Notification::create([
            'user_id'  => auth()->id(),
            'title'    => 'Burial tomorrow',
            'body'     => "{$burial->deceased_name} — {$burial->burial_date}",
            'type'     => 'burial_reminder',
            'link'     => "/burials/{$burial->id}",
        ]);
    });

// Installment reminder (3 days before due)
$threeDays = Carbon::now()->addDays(3)->toDateString();
InstallmentSchedule::whereDate('due_date', $threeDays)
    ->where('status', 'unpaid')
    ->each(function ($schedule) { ... });
```

---

## Section 5 — Public Plot Finder

```php
// routes/web.php — no auth middleware
Route::get('/find', [PublicSearchController::class, 'index'])->name('public.find');
Route::get('/find/search', [PublicSearchController::class, 'search'])->name('public.search');

// PublicSearchController
public function search(Request $request) {
    $q = $request->input('q');
    $results = Burial::with('plot')
        ->whereRaw('MATCH(deceased_name) AGAINST(? IN BOOLEAN MODE)', [$q.'*'])
        ->where('burial_status', 'completed')
        ->select('id','deceased_name','date_of_birth','date_of_death','plot_id')
        ->get()
        ->map(fn($b) => [
            'name'        => $b->deceased_name,
            'dates'       => $b->date_of_birth.' – '.$b->date_of_death,
            'plot_number' => $b->plot->plot_number,
            'section'     => $b->plot->section,
            'lat'         => $b->plot->lat,
            'lng'         => $b->plot->lng,
        ]);
    return response()->json($results);
}
```

---

## Section 6 — Key Commands Reference

```bash
# Create project
composer create-project laravel/laravel memorial-map
cd memorial-map

# Auth
composer require laravel/breeze --dev
php artisan breeze:install blade && php artisan migrate

# Generate all models + migrations
php artisan make:model Plot -m
php artisan make:model Client -m
php artisan make:model Contract -m
php artisan make:model Payment -m
php artisan make:model InstallmentSchedule -m
php artisan make:model Burial -m
php artisan make:model ActivityLog -m

# Controllers
php artisan make:controller PlotController --resource
php artisan make:controller BurialController --resource
php artisan make:controller ContractController --resource
php artisan make:controller PaymentController --resource
php artisan make:controller PublicSearchController

# Observers for auto-logging
php artisan make:observer PlotObserver --model=Plot
php artisan make:observer BurialObserver --model=Burial

# Scheduler commands
php artisan make:command SendBurialReminders
php artisan make:command SendInstallmentReminders

# Run
php artisan migrate --seed
php artisan serve
php artisan schedule:work    # dev — runs scheduler every minute
```

---

*Memorial Map — Laravel 11 + MySQL + Leaflet.js Offline | Total cost: ₱0*
