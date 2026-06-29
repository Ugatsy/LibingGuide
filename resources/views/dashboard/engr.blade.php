<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="text-sm font-medium text-gray-500">Total Plots</div>
        <div class="mt-2 text-3xl font-bold text-gray-900">{{ $totalPlots }}</div>
        <div class="mt-1 text-sm">
            <span class="text-green-600">{{ $availablePlots }} avail</span>
            <span class="mx-1 text-gray-400">·</span>
            <span class="text-yellow-600">{{ $reservedPlots }} reserved</span>
            <span class="mx-1 text-gray-400">·</span>
            <span class="text-red-600">{{ $occupiedPlots }} occ</span>
        </div>
    </div>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="text-sm font-medium text-gray-500">Occupancy Rate</div>
        <div class="mt-2 text-3xl font-bold text-gray-900">{{ $occupancyRate }}%</div>
        <div class="mt-1 w-full bg-gray-200 rounded-full h-2">
            <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $occupancyRate }}%"></div>
        </div>
    </div>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="text-sm font-medium text-gray-500">Map Capacity</div>
        <div class="mt-2 text-3xl font-bold text-gray-900">{{ $totalPlots }}</div>
        <div class="mt-1 text-sm">
            <a href="{{ route('cemetery.admin') }}" class="text-indigo-600 hover:text-indigo-900">Cemetery Map</a>
        </div>
    </div>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="text-sm font-medium text-gray-500">Upcoming Burials</div>
        <div class="mt-2 text-3xl font-bold text-gray-900">{{ $upcomingBurials }}</div>
        <div class="mt-1 text-sm">
            <a href="{{ route('burials.create') }}" class="text-indigo-600 hover:text-indigo-900">Schedule</a>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="text-sm font-medium text-gray-500">Active Contracts</div>
        <div class="mt-2 text-3xl font-bold text-gray-900">{{ $activeContracts }}</div>
    </div>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="text-sm font-medium text-gray-500">Burial Permits Issued</div>
        <div class="mt-2 text-3xl font-bold text-gray-900">{{ $issuedPermits }}</div>
    </div>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="text-sm font-medium text-gray-500">New Inquiries</div>
        <div class="mt-2 text-3xl font-bold text-gray-900">{{ $newInquiries }}</div>
    </div>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="text-sm font-medium text-gray-500">Unread Notifications</div>
        <div class="mt-2 text-3xl font-bold text-gray-900">{{ $unreadNotifications }}</div>
        <div class="mt-1 text-sm">
            <a href="{{ route('notifications.index') }}" class="text-indigo-600 hover:text-indigo-900">View</a>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-900">Recent Burials</h3>
            <a href="{{ route('burials.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">View all</a>
        </div>
        <div class="space-y-3">
            @forelse($recentBurials as $burial)
                <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                    <div>
                        <div class="font-medium text-gray-900">{{ $burial->deceased_name }}</div>
                        <div class="text-sm text-gray-500">{{ $burial->plot?->plot_number }} · {{ $burial->burial_date?->format('M d, Y') }}</div>
                    </div>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full @if($burial->burial_status === 'completed') bg-green-100 text-green-800 @elseif($burial->burial_status === 'scheduled') bg-blue-100 text-blue-800 @else bg-gray-200 text-gray-700 @endif">{{ $burial->burial_status }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-500">No burials recorded yet.</p>
            @endforelse
        </div>
    </div>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-900">Recent Payments</h3>
            <a href="{{ route('payments.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">View all</a>
        </div>
        <div class="space-y-3">
            @forelse($recentPayments as $payment)
                <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                    <div>
                        <div class="font-medium text-gray-900">{{ $payment->contract?->client?->full_name ?? 'N/A' }}</div>
                        <div class="text-sm text-gray-500">₱{{ number_format($payment->amount_paid, 2) }} · {{ $payment->paid_at?->format('M d, Y') }}</div>
                    </div>
                    <span class="text-xs text-gray-500">{{ $payment->payment_type }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-500">No payments recorded yet.</p>
            @endforelse
        </div>
    </div>
</div>
