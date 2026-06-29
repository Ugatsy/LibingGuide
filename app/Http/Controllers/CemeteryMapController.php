<?php

namespace App\Http\Controllers;

use App\Models\Cemetery;
use App\Models\CemeteryPolygon;
use App\Models\Grave;
use App\Models\PathNode;
use App\Services\DijkstraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CemeteryMapController extends Controller
{
    public function adminIndex(Request $request)
    {
        $cemeteries = Cemetery::with('polygon')->get();

        $selectedCemetery = null;
        $polygon = null;

        if ($cemeteries->isNotEmpty()) {
            $cemeteryId = $request->query('cemetery_id', $cemeteries->first()->id);
            $selectedCemetery = $cemeteries->find($cemeteryId) ?? $cemeteries->first();
            $polygon = $selectedCemetery->polygon;
        }

        $graves = Grave::all(['id', 'full_name', 'birth_date', 'death_date', 'section', 'plot_number', 'latitude', 'longitude', 'description', 'image_url']);

        return view('cemetery.admin', [
            'cemeteries' => $cemeteries,
            'selectedCemetery' => $selectedCemetery,
            'polygon' => $polygon?->geojson,
            'graves' => $graves,
        ]);
    }

    public function getCemeteries()
    {
        $cemeteries = Cemetery::with('polygon')->get()->map(fn($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'description' => $c->description,
            'address' => $c->address,
            'lat' => $c->lat,
            'lng' => $c->lng,
            'has_polygon' => $c->polygon !== null,
        ]);

        return response()->json($cemeteries);
    }

    public function saveCemetery(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'entrance_node_id' => 'nullable|exists:path_nodes,id',
        ]);

        $cemetery = Cemetery::create($data);

        return response()->json(['status' => 'ok', 'cemetery' => $cemetery]);
    }

    public function updateCemetery(Request $request, Cemetery $cemetery)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'entrance_node_id' => 'nullable|exists:path_nodes,id',
        ]);

        $cemetery->update($data);

        return response()->json(['status' => 'ok', 'cemetery' => $cemetery]);
    }

    public function deleteCemetery(Cemetery $cemetery)
    {
        $cemetery->polygon()?->delete();
        $cemetery->delete();

        return response()->json(['status' => 'ok']);
    }

    public function savePolygon(Request $request)
    {
        $data = $request->validate([
            'geojson' => 'required|json',
            'area_sqm' => 'required|numeric',
            'area_hectares' => 'required|numeric',
            'cemetery_id' => 'nullable|exists:cemeteries,id',
            'cemetery_name' => 'required_without:cemetery_id|string|max:255',
        ]);

        if ($data['cemetery_id'] ?? null) {
            $cemetery = Cemetery::findOrFail($data['cemetery_id']);
        } else {
            $cemetery = Cemetery::create(['name' => $data['cemetery_name']]);
        }

        $geojson = json_decode($data['geojson']);
        $centroid = $this->getPolygonCentroid($geojson->coordinates[0] ?? []);
        if ($centroid) {
            $cemetery->update(['lat' => $centroid[1], 'lng' => $centroid[0]]);
        }

        $polygon = CemeteryPolygon::updateOrCreate(
            ['cemetery_id' => $cemetery->id],
            [
                'name' => $cemetery->name,
                'geojson' => $data['geojson'],
                'area_sqm' => $data['area_sqm'],
                'area_hectares' => $data['area_hectares'],
            ]
        );

        return response()->json([
            'status' => $polygon->wasRecentlyCreated ? 'created' : 'updated',
            'id' => $polygon->id,
            'cemetery_id' => $cemetery->id,
        ]);
    }

    public function getPolygon(Request $request)
    {
        $cemeteryId = $request->query('cemetery_id');

        if ($cemeteryId) {
            $polygon = CemeteryPolygon::where('cemetery_id', $cemeteryId)->first();
        } else {
            $cemetery = Cemetery::with('polygon')->first();
            $polygon = $cemetery?->polygon;
        }

        if (!$polygon) {
            return response()->json(null);
        }

        return response()->json([
            'id' => $polygon->id,
            'cemetery_id' => $polygon->cemetery_id,
            'geojson' => $polygon->geojson,
            'area_sqm' => $polygon->area_sqm,
            'area_hectares' => $polygon->area_hectares,
        ]);
    }

    public function getGraves()
    {
        $graves = Grave::all();

        $features = $graves->map(function ($grave) {
            return [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [(float) $grave->longitude, (float) $grave->latitude],
                ],
                'properties' => [
                    'id' => $grave->id,
                    'full_name' => $grave->full_name,
                    'birth_date' => $grave->birth_date?->format('Y-m-d'),
                    'death_date' => $grave->death_date?->format('Y-m-d'),
                    'section' => $grave->section,
                    'plot_number' => $grave->plot_number,
                    'description' => $grave->description,
                    'image_url' => $grave->image_url,
                ],
            ];
        });

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }

    public function searchGraves(Request $request)
    {
        $q = $request->input('q');

        if (!$q || strlen($q) < 2) {
            return response()->json([]);
        }

        $results = Grave::where('full_name', 'like', '%' . $q . '%')
            ->orWhere('plot_number', 'like', '%' . $q . '%')
            ->orWhere('section', 'like', '%' . $q . '%')
            ->get();

        return response()->json($results);
    }

    public function importGeoJson(Request $request)
    {
        $request->validate([
            'geojson' => 'required|json',
            'cemetery_id' => 'nullable|exists:cemeteries,id',
            'cemetery_name' => 'required_without:cemetery_id|string|max:255',
        ]);

        $data = json_decode($request->geojson);

        if (!isset($data->features) || !is_array($data->features)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid GeoJSON format'], 422);
        }

        if ($request->cemetery_id ?? null) {
            $cemetery = Cemetery::findOrFail($request->cemetery_id);
        } else {
            $cemetery = Cemetery::create(['name' => $request->cemetery_name]);
        }

        $polygonFeature = null;
        $graveFeatures = [];

        foreach ($data->features as $feature) {
            if (isset($feature->geometry)) {
                if ($feature->geometry->type === 'Polygon' || $feature->geometry->type === 'MultiPolygon') {
                    $polygonFeature = $feature;
                } elseif ($feature->geometry->type === 'Point') {
                    $graveFeatures[] = $feature;
                }
            }
        }

        if ($polygonFeature) {
            $areaSqm = 0;
            $areaHectares = 0;

            try {
                $coords = $polygonFeature->geometry->coordinates;
                $areaSqm = $this->calculateArea($coords[0]);
                $areaHectares = $areaSqm / 10000;
            } catch (\Exception $e) {
                // fallback
            }

            $geojson = json_encode($polygonFeature->geometry);

            $centroid = $this->getPolygonCentroid($polygonFeature->geometry->coordinates[0] ?? []);

            if ($centroid) {
                $cemetery->update(['lat' => $centroid[1], 'lng' => $centroid[0]]);
            }

            CemeteryPolygon::updateOrCreate(
                ['cemetery_id' => $cemetery->id],
                [
                    'name' => $cemetery->name,
                    'geojson' => $geojson,
                    'area_sqm' => $areaSqm,
                    'area_hectares' => $areaHectares,
                ]
            );
        }

        foreach ($graveFeatures as $gf) {
            $props = $gf->properties ?? [];
            $coords = $gf->geometry->coordinates;

            Grave::updateOrCreate(
                ['full_name' => $props->full_name ?? 'Unknown', 'plot_number' => $props->plot_number ?? null],
                [
                    'full_name' => $props->full_name ?? 'Unknown',
                    'birth_date' => $props->birth_date ?? null,
                    'death_date' => $props->death_date ?? null,
                    'section' => $props->section ?? null,
                    'plot_number' => $props->plot_number ?? null,
                    'latitude' => $coords[1],
                    'longitude' => $coords[0],
                    'description' => $props->description ?? null,
                    'image_url' => $props->image_url ?? null,
                ]
            );
        }

        return response()->json([
            'status' => 'ok',
            'polygon_imported' => $polygonFeature ? true : false,
            'graves_imported' => count($graveFeatures),
        ]);
    }

    private function getPolygonCentroid(array $ring): ?array
    {
        if (empty($ring)) return null;

        $sumX = 0;
        $sumY = 0;
        $count = count($ring);

        foreach ($ring as $coord) {
            $sumX += $coord[0];
            $sumY += $coord[1];
        }

        return [$sumX / $count, $sumY / $count];
    }

    private function calculateArea($ring)
    {
        $n = count($ring);
        $area = 0;

        for ($i = 0; $i < $n; $i++) {
            $x1 = deg2rad($ring[$i][0]);
            $y1 = deg2rad($ring[$i][1]);
            $x2 = deg2rad($ring[($i + 1) % $n][0]);
            $y2 = deg2rad($ring[($i + 1) % $n][1]);

            $area += deg2rad($ring[$i][1]) * cos(deg2rad($ring[$i][0])) * $x2 - deg2rad($ring[$i][1]) * cos(deg2rad($ring[$i][0])) * $x1;
        }

        return abs($area * 6378137 * 6378137 / 2);
    }

    public function seedGraves(Request $request)
    {
        $cemeteryId = $request->query('cemetery_id');
        $polygon = null;

        if ($cemeteryId) {
            $polygon = CemeteryPolygon::where('cemetery_id', $cemeteryId)->first();
        } else {
            $polygon = CemeteryPolygon::first();
        }

        if (!$polygon) {
            return response()->json(['status' => 'error', 'message' => 'No cemetery boundary polygon found. Draw and save one first.'], 400);
        }

        $geojson = $polygon->geojson;
        $ring = $geojson->coordinates[0];

        $names = [
            ['Juan Dela Cruz', '1930-05-15', '2020-01-10', 'Beloved father and grandfather'],
            ['Maria Santos', '1945-08-22', '2019-11-03', 'Devoted mother and teacher'],
            ['Pedro Reyes', '1920-03-10', '2005-07-19', 'Veteran and community leader'],
            ['Ana Gonzales', '1950-12-01', '2022-04-15', 'Loving wife and entrepreneur'],
            ['Elena Cruz', '1935-02-14', '2018-09-22', 'Cherished grandmother'],
            ['Antonio Bautista', '1925-07-30', '2010-06-11', 'Farmer and family man'],
            ['Sofia Villanueva', '1940-11-25', '2021-03-08', 'Nurse and mother of three'],
            ['Luzviminda Ramos', '1955-04-20', '2023-01-05', 'Beloved sister and friend'],
            ['Nenita Garcia', '1960-09-10', '2020-12-25', 'Devoted mother and OFW'],
            ['Gregorio Fernandez', '1918-01-01', '1998-08-15', 'War veteran and teacher'],
            ['Carmen Lopez', '1948-06-06', '2019-05-20', 'Beloved wife and mother'],
            ['Ramon Magsaysay', '1907-08-31', '1957-03-17', 'Former president (memorial plaque)'],
            ['Jose Rizal', '1861-06-19', '1896-12-30', 'National hero (memorial marker)'],
            ['Fernando Poe Jr.', '1939-08-20', '2004-12-14', 'Legendary actor (memorial marker)'],
            ['Emilio Aguinaldo', '1869-03-22', '1964-02-06', 'First Philippine president (historical marker)'],
        ];

        $sections = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

        Grave::truncate();

        $created = 0;
        foreach ($names as $i => $data) {
            $point = $this->randomPointInPolygon($ring);
            if (!$point) continue;

            $section = $sections[$i % count($sections)];
            $plotNum = $section . '-' . str_pad(floor($i / count($sections)) + 1, 3, '0', STR_PAD_LEFT);

            Grave::create([
                'full_name' => $data[0],
                'birth_date' => $data[1],
                'death_date' => $data[2],
                'section' => $section,
                'plot_number' => $plotNum,
                'latitude' => $point[1],
                'longitude' => $point[0],
                'description' => $data[3],
            ]);

            $created++;
        }

        return response()->json(['status' => 'ok', 'count' => $created]);
    }

    public function saveGrave(Request $request)
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'birth_date' => 'nullable|date',
            'death_date' => 'nullable|date',
            'section' => 'nullable|string|max:100',
            'plot_number' => 'nullable|string|max:50',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string|max:500',
        ]);

        $grave = Grave::create($data);

        return response()->json(['status' => 'ok', 'grave' => $grave]);
    }

    private function randomPointInPolygon($ring)
    {
        $minLng = $maxLng = $ring[0][0];
        $minLat = $maxLat = $ring[0][1];

        foreach ($ring as $coord) {
            if ($coord[0] < $minLng) $minLng = $coord[0];
            if ($coord[0] > $maxLng) $maxLng = $coord[0];
            if ($coord[1] < $minLat) $minLat = $coord[1];
            if ($coord[1] > $maxLat) $maxLat = $coord[1];
        }

        $attempts = 0;
        while ($attempts < 200) {
            $lng = $minLng + mt_rand() / mt_getrandmax() * ($maxLng - $minLng);
            $lat = $minLat + mt_rand() / mt_getrandmax() * ($maxLat - $minLat);

            if ($this->pointInPolygon([$lng, $lat], $ring)) {
                return [$lng, $lat];
            }
            $attempts++;
        }

        return null;
    }

    private function pointInPolygon($point, $ring)
    {
        $x = $point[0];
        $y = $point[1];
        $inside = false;
        $n = count($ring);

        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $xi = $ring[$i][0];
            $yi = $ring[$i][1];
            $xj = $ring[$j][0];
            $yj = $ring[$j][1];

            $intersect = (($yi > $y) !== ($yj > $y))
                && ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi);

            if ($intersect) $inside = !$inside;
        }

        return $inside;
    }

    public function findPathToGrave(Request $request, DijkstraService $dijkstra)
    {
        $endLat = $request->input('end_lat');
        $endLng = $request->input('end_lng');
        $cemeteryId = $request->input('cemetery_id');

        if (!$endLat || !$endLng) {
            return response()->json(['error' => 'Missing end coordinates'], 422);
        }

        // Find the cemetery entrance node
        $entranceNode = null;

        if ($cemeteryId) {
            $cemetery = Cemetery::find($cemeteryId);
            if ($cemetery && $cemetery->entrance_node_id) {
                $entranceNode = PathNode::find($cemetery->entrance_node_id);
            }
        }

        // Fallback: find first entrance node in the system
        if (!$entranceNode) {
            $entranceNode = PathNode::where('type', 'entrance')->first();
        }

        if (!$entranceNode) {
            return response()->json(['error' => 'No entrance node configured for any cemetery'], 404);
        }

        $allNodes = PathNode::all();
        if ($allNodes->isEmpty()) {
            return response()->json(['error' => 'No pathway nodes configured'], 404);
        }

        // Find nearest node to the end (grave) location
        $nearestToEnd = null;
        $minEndDist = INF;

        foreach ($allNodes as $node) {
            $dToEnd = $dijkstra->calculateDistance($endLat, $endLng, $node->lat, $node->lng);

            if ($dToEnd < $minEndDist) {
                $minEndDist = $dToEnd;
                $nearestToEnd = $node;
            }
        }

        // Find path from entrance node to the nearest node of the grave
        $path = $dijkstra->findShortestPath($entranceNode, $nearestToEnd);

        if (!$path) {
            return response()->json(['error' => 'No path found from entrance to grave location'], 404);
        }

        $coords = $path['path']->map(fn($n) => [$n->lng, $n->lat])->toArray();

        // Prepend entrance node and append grave location to the coordinates
        array_unshift($coords, [(float) $entranceNode->lng, (float) $entranceNode->lat]);
        $coords[] = [(float) $endLng, (float) $endLat];

        // Edge weights are in meters, Haversine returns km — convert to meters
        $lastNodeToGraveDistMeters = $dijkstra->calculateDistance(
            $path['path']->last()->lat, $path['path']->last()->lng,
            $endLat, $endLng
        ) * 1000;

        $totalDistanceMeters = $path['distance'] + $lastNodeToGraveDistMeters;

        return response()->json([
            'path' => [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'LineString',
                    'coordinates' => $coords,
                ],
                'properties' => [
                    'distance' => round($totalDistanceMeters / 1000, 4),
                    'distance_text' => $totalDistanceMeters < 1000
                        ? round($totalDistanceMeters, 0) . ' m'
                        : round($totalDistanceMeters / 1000, 2) . ' km',
                ],
            ],
            'start_node' => ['id' => $entranceNode->id, 'lat' => $entranceNode->lat, 'lng' => $entranceNode->lng],
            'end_node' => ['id' => $nearestToEnd->id, 'lat' => $nearestToEnd->lat, 'lng' => $nearestToEnd->lng],
        ]);
    }
}
