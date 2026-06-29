<?php

namespace App\Http\Controllers;

use App\Models\Cemetery;
use App\Models\Plot;
use App\Http\Requests\StorePlotRequest;
use Illuminate\Http\Request;

class PlotController extends Controller
{
    public function index()
    {
        $plots = Plot::withCount('burials')->orderBy('plot_number')->get();
        $plotData = $plots->map(fn($p) => [
            'id' => $p->id,
            'plot_number' => $p->plot_number,
            'section' => $p->section,
            'lat' => $p->lat,
            'lng' => $p->lng,
            'shape' => $p->shape,
            'lot_type' => $p->lot_type,
            'dimension' => $p->dimension,
            'status' => $p->status,
            'burials_count' => $p->burials_count,
            'capacity' => $p->capacity,
            'current_occupants' => $p->current_occupants,
            'price' => $p->price,
        ]);
        $cemetery = Cemetery::with('polygon')->first();
        $boundary = $cemetery?->polygon?->geojson;
        return view('plots.index', compact('plots', 'plotData', 'boundary'));
    }

    public function create()
    {
        $cemetery = Cemetery::with('polygon')->first();
        $boundary = $cemetery?->polygon?->geojson;
        return view('plots.create', compact('boundary'));
    }

    public function store(StorePlotRequest $request)
    {
        $data = $request->validated();
        if (isset($data['shape']) && is_string($data['shape'])) {
            $data['shape'] = json_decode($data['shape'], true);
        }
        $plot = Plot::create($data);
        if ($request->isJson()) {
            return response()->json(['id' => $plot->id, 'plot_number' => $plot->plot_number], 201);
        }
        return redirect()->route('plots.index')->with('success', 'Plot created.');
    }

    public function show(Plot $plot)
    {
        $plot->load('burials', 'contracts.client');
        $cemetery = Cemetery::with('polygon')->first();
        $boundary = $cemetery?->polygon?->geojson;
        return view('plots.show', compact('plot', 'boundary'));
    }

    public function edit(Plot $plot)
    {
        $cemetery = Cemetery::with('polygon')->first();
        $boundary = $cemetery?->polygon?->geojson;
        return view('plots.edit', compact('plot', 'boundary'));
    }

    public function update(StorePlotRequest $request, Plot $plot)
    {
        $data = $request->validated();
        if (isset($data['shape']) && is_string($data['shape'])) {
            $data['shape'] = json_decode($data['shape'], true);
        }
        $plot->update($data);
        return redirect()->route('plots.index')->with('success', 'Plot updated.');
    }

    public function destroy(Plot $plot)
    {
        if ($plot->burials()->exists()) {
            return back()->with('error', 'Cannot delete plot with existing burials.');
        }
        $plot->delete();
        return redirect()->route('plots.index')->with('success', 'Plot deleted.');
    }

    public function updatePosition(Request $request, Plot $plot)
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $plot->update(['lat' => $request->lat, 'lng' => $request->lng]);
        return response()->json(['success' => true]);
    }
}
