@extends('layouts.public')

@section('title', 'Memorial Lots — HIMLAYAN')

@section('content')
    <section class="pt-32 pb-24 bg-stone-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <p class="text-emerald-700 font-semibold text-sm tracking-widest uppercase mb-3">Memorial Lots</p>
                <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">Browse Available Memorial Lots</h1>
                <p class="text-gray-600 max-w-2xl mx-auto">View our available lots on the interactive map and find the perfect resting place for your loved one.</p>
                <div class="mt-6 inline-flex items-center gap-2 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-sm text-amber-800">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    <span>Just looking for a lot? You're in the right place. If you'd prefer a <strong>plan that includes a lot plus services</strong>, browse our <a href="{{ route('public.plans') }}" class="underline font-medium hover:text-amber-900">Pre-Need Plans</a> instead.</span>
                </div>
            </div>

            <div class="grid lg:grid-cols-3 gap-8">
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-sm p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">Available Lots</h3>
                        <div id="plot-list" class="space-y-2 max-h-[500px] overflow-y-auto">
                            @forelse($plots->where('status', 'available') as $plot)
                                <div class="plot-entry p-3 rounded-lg border border-gray-200 hover:border-emerald-300 cursor-pointer transition-colors" data-plot-id="{{ $plot->id }}">
                                    <div class="flex justify-between items-center">
                                        <span class="font-medium text-gray-900">{{ $plot->plot_number }}</span>
                                        <span class="text-sm font-semibold text-emerald-700">₱{{ number_format($plot->price, 2) }}</span>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1">{{ $plot->section ?? 'No section' }}</p>
                                </div>
                            @empty
                                <p class="text-gray-500 text-sm text-center py-8">No available lots at this time.</p>
                            @endforelse
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <a href="{{ route('public.reserve.form', 'lot') }}" class="block w-full text-center px-4 py-3 bg-emerald-700 text-white font-semibold rounded-lg hover:bg-emerald-600 transition-colors">Reserve a Lot</a>
                        </div>
                    </div>
                </div>
                <div class="lg:col-span-2">
                    <div id="map" class="w-full h-[600px] rounded-2xl shadow-sm overflow-hidden border border-gray-200"></div>
                </div>
            </div>
        </div>
    </section>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const PLOTS = @json($plotData);
        const map = L.map('map', {
            center: [16.5253, 121.1906],
            zoom: 19,
            minZoom: 17,
            maxZoom: 20,
            maxBounds: L.latLngBounds([16.5217, 121.1862], [16.5290, 121.1951]),
            maxBoundsViscosity: 1.0,
        });

        L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            attribution: '&copy; Google',
        }).addTo(map);

        const plotLayers = [];
        PLOTS.forEach(plot => {
            const color = plot.status === 'available' ? '#22c55e' : plot.status === 'reserved' ? '#f59e0b' : '#ef4444';
            const shape = plot.shape?.geometry || plot.shape;
            if (shape && shape.coordinates) {
                const geojson = shape.type ? shape : { type: 'Feature', geometry: shape, properties: {} };
                const layer = L.geoJSON(geojson, {
                    style: { color, fillColor: color, weight: 4, fillOpacity: 0.25 },
                }).bindPopup(`<b>${plot.plot_number}</b><br>${plot.section ?? ''}<br>Status: ${plot.status}<br>Price: ₱${Number(plot.price).toLocaleString()}`);
                layer._plotId = plot.id;
                layer.addTo(map);
                plotLayers.push(layer);
            } else if (plot.lat && plot.lng) {
                const marker = L.circleMarker([plot.lat, plot.lng], {
                    radius: 8, color, fillColor: color, fillOpacity: 0.6, weight: 2,
                }).bindPopup(`<b>${plot.plot_number}</b><br>${plot.section ?? ''}<br>Status: ${plot.status}<br>Price: ₱${Number(plot.price).toLocaleString()}`);
                marker._plotId = plot.id;
                marker.addTo(map);
                plotLayers.push(marker);
            }
        });

        document.querySelectorAll('.plot-entry').forEach(el => {
            el.addEventListener('click', function() {
                const id = parseInt(this.dataset.plotId);
                const layer = plotLayers.find(l => l._plotId === id);
                if (layer) {
                    const center = layer.getBounds ? layer.getBounds().getCenter() : layer.getLatLng();
                    map.flyTo(center, 18, { duration: 0.5 });
                    map.once('moveend', () => layer.openPopup());
                }
            });
        });
    </script>
@endsection
