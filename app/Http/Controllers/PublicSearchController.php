<?php

namespace App\Http\Controllers;

use App\Models\Burial;
use Illuminate\Http\Request;

class PublicSearchController extends Controller
{
    public function index()
    {
        return view('public.find');
    }

    public function allMarkers()
    {
        $burials = Burial::with('plot')
            ->where('burial_status', 'completed')
            ->select('id', 'deceased_name', 'date_of_birth', 'date_of_death', 'plot_id')
            ->get()
            ->filter(fn($b) => $b->plot && $b->plot->lat && $b->plot->lng)
            ->values();

        $features = $burials->map(fn($b) => [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [(float) $b->plot->lng, (float) $b->plot->lat],
            ],
            'properties' => [
                'id' => $b->id,
                'full_name' => $b->deceased_name,
                'birth_date' => $b->date_of_birth?->format('Y-m-d'),
                'death_date' => $b->date_of_death?->format('Y-m-d'),
                'section' => $b->plot->section,
                'plot_number' => $b->plot->plot_number,
                'description' => null,
                'image_url' => null,
            ],
        ]);

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }

    public function search(Request $request)
    {
        $q = $request->input('q');

        if (!$q || strlen($q) < 2) {
            return response()->json([]);
        }

        $results = Burial::with('plot')
            ->where(function ($query) use ($q) {
                $query->whereRaw('MATCH(deceased_name) AGAINST(? IN BOOLEAN MODE)', [$q . '*'])
                      ->orWhere('deceased_name', 'like', '%' . $q . '%')
                      ->orWhereHas('plot', function ($q2) use ($q) {
                          $q2->where('plot_number', 'like', '%' . $q . '%');
                      });
            })
            ->where('burial_status', 'completed')
            ->select('id', 'deceased_name', 'date_of_birth', 'date_of_death', 'plot_id')
            ->get()
            ->filter(fn($b) => $b->plot)
            ->map(fn($b) => [
                'name'        => $b->deceased_name,
                'dates'       => ($b->date_of_birth?->format('Y-m-d') ?? '?') . ' – ' . $b->date_of_death->format('Y-m-d'),
                'plot_number' => $b->plot->plot_number,
                'section'     => $b->plot->section,
                'lat'         => $b->plot->lat,
                'lng'         => $b->plot->lng,
            ]);

        return response()->json($results);
    }
}
