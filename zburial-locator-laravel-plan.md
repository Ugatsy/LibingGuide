# Burial Spot Locator — Laravel Web System Plan

> A web-based burial spot management system with a draggable SVG map, built on Laravel 11 + MySQL.

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 11 (PHP 8.2+) |
| Database | MySQL 8.0+ |
| Frontend | Blade Templates + Vanilla JS |
| Map Engine | Custom SVG Canvas |
| Auth | Laravel Breeze |
| Deployment | Render / Railway / Shared Hosting |

---

## Project Structure

```
burial-locator/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── BurialSpotController.php
│   │   └── Requests/
│   │       └── StoreBurialSpotRequest.php
│   └── Models/
│       └── BurialSpot.php
├── database/
│   ├── migrations/
│   │   └── xxxx_create_burial_spots_table.php
│   └── seeders/
│       └── BurialSpotSeeder.php
├── public/
│   └── js/
│       └── map.js
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php
│       └── burial/
│           ├── index.blade.php
│           └── _modal.blade.php
└── routes/
    ├── web.php
    └── api.php
```

---

## Phase 1 — Project Setup (Day 1–2)

### Install Laravel

```bash
composer create-project laravel/laravel burial-locator
cd burial-locator
```

### Configure `.env` for MySQL

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=burial_locator
DB_USERNAME=root
DB_PASSWORD=your_password
```

### Create the MySQL database

```sql
CREATE DATABASE burial_locator CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Install Laravel Breeze (Auth)

```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
php artisan migrate
npm install && npm run dev
```

---

## Phase 2 — Database & Model (Day 3–4)

### Migration

```bash
php artisan make:model BurialSpot -m
```

**`xxxx_create_burial_spots_table.php`**

```php
Schema::create('burial_spots', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('plot_number')->unique();
    $table->string('section')->nullable();
    $table->year('birth_year')->nullable();
    $table->year('death_year')->nullable();
    $table->enum('status', ['occupied', 'reserved', 'available'])->default('available');
    $table->text('notes')->nullable();
    $table->decimal('map_x', 8, 2)->default(0); // SVG x coordinate
    $table->decimal('map_y', 8, 2)->default(0); // SVG y coordinate
    $table->timestamps();
});
```

### Model — `BurialSpot.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BurialSpot extends Model
{
    protected $fillable = [
        'name',
        'plot_number',
        'section',
        'birth_year',
        'death_year',
        'status',
        'notes',
        'map_x',
        'map_y',
    ];

    protected $casts = [
        'map_x' => 'float',
        'map_y' => 'float',
    ];
}
```

### Seeder

```bash
php artisan make:seeder BurialSpotSeeder
php artisan db:seed --class=BurialSpotSeeder
```

```php
// database/seeders/BurialSpotSeeder.php
BurialSpot::insert([
    ['name' => 'Maria Santos',  'plot_number' => 'A-01', 'section' => 'Block 1', 'birth_year' => 1935, 'death_year' => 2010, 'status' => 'occupied', 'map_x' => 110, 'map_y' => 90],
    ['name' => 'Jose Reyes',    'plot_number' => 'A-02', 'section' => 'Block 1', 'birth_year' => 1942, 'death_year' => 2015, 'status' => 'occupied', 'map_x' => 170, 'map_y' => 90],
    ['name' => 'Plot B-01',     'plot_number' => 'B-01', 'section' => 'Block 2', 'birth_year' => null,  'death_year' => null,  'status' => 'available','map_x' => 110, 'map_y' => 160],
]);
```

---

## Phase 3 — Routes & Controller (Day 5–6)

### Routes — `routes/web.php`

```php
use App\Http\Controllers\BurialSpotController;

Route::middleware('auth')->group(function () {
    Route::resource('burial-spots', BurialSpotController::class);
    Route::patch('burial-spots/{burialSpot}/position', [BurialSpotController::class, 'updatePosition'])
         ->name('burial-spots.position');
});
```

### Controller — `BurialSpotController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\BurialSpot;
use App\Http\Requests\StoreBurialSpotRequest;
use Illuminate\Http\Request;

class BurialSpotController extends Controller
{
    public function index()
    {
        $spots = BurialSpot::orderBy('plot_number')->get();
        return view('burial.index', compact('spots'));
    }

    public function store(StoreBurialSpotRequest $request)
    {
        BurialSpot::create($request->validated());
        return back()->with('success', 'Burial spot added.');
    }

    public function update(StoreBurialSpotRequest $request, BurialSpot $burialSpot)
    {
        $burialSpot->update($request->validated());
        return back()->with('success', 'Burial spot updated.');
    }

    public function destroy(BurialSpot $burialSpot)
    {
        $burialSpot->delete();
        return back()->with('success', 'Burial spot deleted.');
    }

    // Drag-and-drop position save
    public function updatePosition(Request $request, BurialSpot $burialSpot)
    {
        $request->validate([
            'x' => 'required|numeric|min:0|max:520',
            'y' => 'required|numeric|min:0|max:380',
        ]);

        $burialSpot->update([
            'map_x' => $request->x,
            'map_y' => $request->y,
        ]);

        return response()->json(['success' => true, 'x' => $burialSpot->map_x, 'y' => $burialSpot->map_y]);
    }
}
```

### Form Request — `StoreBurialSpotRequest.php`

```php
public function rules(): array
{
    return [
        'name'        => 'required|string|max:255',
        'plot_number' => 'required|string|max:50|unique:burial_spots,plot_number,' . $this->burialSpot?->id,
        'section'     => 'nullable|string|max:100',
        'birth_year'  => 'nullable|integer|min:1800|max:' . date('Y'),
        'death_year'  => 'nullable|integer|min:1800|max:' . date('Y'),
        'status'      => 'required|in:occupied,reserved,available',
        'notes'       => 'nullable|string|max:500',
    ];
}
```

---

## Phase 4 — Frontend Map (Day 7–10)

### Blade View — `index.blade.php` (structure)

```html
@extends('layouts.app')

@section('content')
<div id="app">
  <!-- Sidebar -->
  <div id="sidebar">
    <input type="text" id="search-input" placeholder="Search name or plot…">
    <div id="burial-list">
      @foreach($spots as $spot)
        <div class="entry" data-id="{{ $spot->id }}">
          <strong>{{ $spot->name }}</strong>
          <span>{{ $spot->plot_number }}</span>
          <span class="badge badge-{{ $spot->status }}">{{ $spot->status }}</span>
        </div>
      @endforeach
    </div>
    <button onclick="openModal()">+ Add burial spot</button>
  </div>

  <!-- Map Canvas -->
  <div id="map-area">
    <svg id="map-svg" viewBox="0 0 520 380">
      <!-- grid, labels -->
      <g id="markers-layer"></g>
    </svg>
  </div>
</div>

<!-- Pass PHP data to JS -->
<script>
  const SPOTS = @json($spots);
  const UPDATE_POSITION_URL = "{{ route('burial-spots.position', ':id') }}";
  const CSRF_TOKEN = "{{ csrf_token() }}";
</script>
<script src="{{ asset('js/map.js') }}"></script>
@endsection
```

### JS Drag-and-Drop Position Save — `public/js/map.js`

```javascript
// After dragend, save new position to Laravel
async function savePosition(id, x, y) {
  const url = UPDATE_POSITION_URL.replace(':id', id);
  await fetch(url, {
    method: 'PATCH',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': CSRF_TOKEN,
    },
    body: JSON.stringify({ x, y }),
  });
}
```

---

## Phase 5 — CRUD Forms (Day 11–13)

### Add / Edit Modal Form (Blade partial)

```html
<!-- resources/views/burial/_modal.blade.php -->
<div id="modal-overlay">
  <form method="POST" action="{{ route('burial-spots.store') }}">
    @csrf
    <input name="name"         type="text"   placeholder="Full name" required>
    <input name="plot_number"  type="text"   placeholder="e.g. A-12" required>
    <input name="section"      type="text"   placeholder="e.g. Block 3, Row B">
    <input name="birth_year"   type="number" placeholder="Birth year">
    <input name="death_year"   type="number" placeholder="Death year">
    <select name="status">
      <option value="occupied">Occupied</option>
      <option value="reserved">Reserved</option>
      <option value="available">Available</option>
    </select>
    <textarea name="notes" placeholder="Notes"></textarea>
    <button type="submit">Save</button>
  </form>
</div>
```

### Delete Button

```html
<form method="POST" action="{{ route('burial-spots.destroy', $spot) }}">
  @csrf
  @method('DELETE')
  <button onclick="return confirm('Delete this record?')">Delete</button>
</form>
```

---

## Phase 6 — Polish & Deploy (Day 14–16)

### Features to add before launch

- [ ] Status filter dropdown (All / Occupied / Reserved / Available)
- [ ] Print / Export map (browser `window.print()` or `dompdf`)
- [ ] Pagination on the sidebar list (if records grow large)
- [ ] Flash message banners for success / error

### Deploy to Render with MySQL

```bash
# Production optimizations
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

**Render environment variables:**

```env
APP_ENV=production
APP_KEY=base64:...          # php artisan key:generate
APP_URL=https://your-app.onrender.com

DB_CONNECTION=mysql
DB_HOST=your-mysql-host     # e.g. PlanetScale or Railway MySQL
DB_PORT=3306
DB_DATABASE=burial_locator
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

> **Recommended MySQL host for Render:** [PlanetScale](https://planetscale.com) (free tier, MySQL-compatible) or [Railway](https://railway.app) (built-in MySQL).

---

## Database Schema Summary

```
burial_spots
┌────────────────┬───────────────────────────────────────┐
│ Column         │ Type / Notes                          │
├────────────────┼───────────────────────────────────────┤
│ id             │ BIGINT UNSIGNED, PK, AUTO_INCREMENT   │
│ name           │ VARCHAR(255)                          │
│ plot_number    │ VARCHAR(50), UNIQUE                   │
│ section        │ VARCHAR(100), NULLABLE                │
│ birth_year     │ YEAR, NULLABLE                        │
│ death_year     │ YEAR, NULLABLE                        │
│ status         │ ENUM(occupied, reserved, available)   │
│ notes          │ TEXT, NULLABLE                        │
│ map_x          │ DECIMAL(8,2) — SVG x coordinate       │
│ map_y          │ DECIMAL(8,2) — SVG y coordinate       │
│ created_at     │ TIMESTAMP                             │
│ updated_at     │ TIMESTAMP                             │
└────────────────┴───────────────────────────────────────┘
```

---

## Quick Command Reference

```bash
# Start fresh
composer create-project laravel/laravel burial-locator
cd burial-locator

# Auth scaffold
composer require laravel/breeze --dev
php artisan breeze:install blade && php artisan migrate

# Generate model + migration
php artisan make:model BurialSpot -m

# Generate controller
php artisan make:controller BurialSpotController --resource

# Generate form request
php artisan make:request StoreBurialSpotRequest

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed --class=BurialSpotSeeder

# Run local server
php artisan serve
```

---

*Generated for the Burial Spot Locator capstone project — Laravel 11 + MySQL*
