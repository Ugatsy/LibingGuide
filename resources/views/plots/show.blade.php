<x-app-layout>
    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Plot: {{ $plot->plot_number }}</h1>

            <div class="grid lg:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <dl class="grid grid-cols-2 gap-4">
                        <div><dt class="text-sm text-gray-600">Plot Number</dt><dd class="font-medium">{{ $plot->plot_number }}</dd></div>
                        <div><dt class="text-sm text-gray-600">Section</dt><dd class="font-medium">{{ $plot->section ?? '—' }}</dd></div>
                        <div><dt class="text-sm text-gray-600">Lot Type</dt><dd class="font-medium capitalize">{{ $plot->lot_type ?? 'Individual' }}</dd></div>
                        <div><dt class="text-sm text-gray-600">Dimension</dt><dd class="font-medium">{{ $plot->dimension ?? '—' }}</dd></div>
                        <div><dt class="text-sm text-gray-600">Coordinates</dt><dd class="font-medium text-sm">{{ $plot->lat }}, {{ $plot->lng }}</dd></div>
                        <div><dt class="text-sm text-gray-600">Status</dt><dd><span class="px-2 py-1 text-xs font-semibold rounded-full
                            @if($plot->status === 'available') bg-green-100 text-green-800
                            @elseif($plot->status === 'reserved') bg-yellow-100 text-yellow-800
                            @elseif($plot->status === 'full') bg-red-200 text-red-900
                            @else bg-red-100 text-red-800 @endif
                        ">{{ $plot->status }}</span></dd></div>
                        <div><dt class="text-sm text-gray-600">Capacity</dt><dd class="font-medium">{{ $plot->capacity }}</dd></div>
                        <div><dt class="text-sm text-gray-600">Current Occupants</dt><dd class="font-medium">{{ $plot->current_occupants }}</dd></div>
                        <div><dt class="text-sm text-gray-600">Price</dt><dd class="font-medium">₱{{ number_format($plot->price, 2) }}</dd></div>
                    </dl>
                    @if($plot->notes)
                        <div class="mt-4 p-3 bg-gray-50 rounded text-sm">
                            <span class="text-gray-500">Notes:</span>
                            <p class="mt-1">{{ $plot->notes }}</p>
                        </div>
                    @endif
                    <div class="mt-6 flex gap-4">
                        <a href="{{ route('plots.edit', $plot) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                        <form method="POST" action="{{ route('plots.destroy', $plot) }}" onsubmit="return confirm('Delete this plot?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                        </form>
                    </div>
                </div>

                <div>
                    <div id="map" style="height: 350px; border: 2px solid #e5e7eb; border-radius: 6px;"></div>
                    @if($plot->shape)
                        <p class="text-xs text-gray-500 mt-2">Shape: {{ count($plot->shape['coordinates'][0] ?? []) }} vertices</p>
                    @else
                        <p class="text-xs text-gray-500 mt-2">No shape drawn — pin location only.</p>
                    @endif
                </div>
            </div>

            @if($plot->burials->count())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mt-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Burials in this plot</h3>
                    <table class="w-full text-sm">
                        <thead><tr class="border-b"><th class="text-left py-2">Name</th><th class="text-left">Date</th><th class="text-left">Status</th></tr></thead>
                        <tbody>
                            @foreach($plot->burials as $burial)
                                <tr class="border-b">
                                    <td class="py-2">{{ $burial->deceased_name }}</td>
                                    <td>{{ $burial->burial_date->format('M d, Y') }}</td>
                                    <td><span class="text-xs px-2 py-1 rounded-full
                                        @if($burial->burial_status === 'completed') bg-green-100 text-green-800
                                        @elseif($burial->burial_status === 'scheduled') bg-blue-100 text-blue-800
                                        @else bg-gray-100 text-gray-800 @endif
                                    ">{{ $burial->burial_status }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const BOUNDARY = @json($boundary);
        const map = L.map('map', {
            center: [{{ $plot->lat }}, {{ $plot->lng }}],
            zoom: 20,
            minZoom: 17,
            maxZoom: 21,
            maxBounds: L.latLngBounds([16.5217, 121.1862], [16.5290, 121.1951]),
            maxBoundsViscosity: 1.0,
            zoomControl: false,
        });

        L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            attribution: '&copy; Google',
        }).addTo(map);

        @php $geom = $plot->shape['geometry'] ?? $plot->shape; @endphp
        @if($geom && !empty($geom['coordinates']))
            const shape = @json($geom);
            const statusColor = '{{ $plot->status === 'available' ? '#22c55e' : ($plot->status === 'reserved' ? '#f59e0b' : '#ef4444') }}';
            L.geoJSON(shape, {
                style: { color: statusColor, fillColor: statusColor, weight: 4, fillOpacity: 0.3 },
            }).addTo(map);
            if (BOUNDARY) {
                const g = BOUNDARY.geometry || BOUNDARY;
                L.geoJSON(g, {
                    style: { color: '#059669', weight: 3, fillColor: '#059669', fillOpacity: 0.08 },
                }).addTo(map);
            }
            map.fitBounds(L.geoJSON(shape).getBounds().pad(0.1));
        @else
            if (BOUNDARY) {
                const g = BOUNDARY.geometry || BOUNDARY;
                L.geoJSON(g, {
                    style: { color: '#059669', weight: 3, fillColor: '#059669', fillOpacity: 0.08 },
                }).addTo(map);
            }
            L.circleMarker([{{ $plot->lat }}, {{ $plot->lng }}], {
                radius: 8,
                color: '#6366f1',
                fillColor: '#6366f1',
                fillOpacity: 0.6,
                weight: 2,
            }).addTo(map).bindPopup('<b>{{ $plot->plot_number }}</b>');
        @endif
    </script>
</x-app-layout>