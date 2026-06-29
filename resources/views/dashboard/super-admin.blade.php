<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="text-sm font-medium text-gray-500">Total Plots</div>
        <div class="mt-2 text-3xl font-bold text-gray-900">{{ $totalPlots }}</div>
        <div class="mt-1 text-sm">
            <span class="text-green-600">{{ $availablePlots }} available</span>
            <span class="mx-1 text-gray-400">·</span>
            <span class="text-yellow-600">{{ $reservedPlots }} reserved</span>
            <span class="mx-1 text-gray-400">·</span>
            <span class="text-red-600">{{ $occupiedPlots }} occupied</span>
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
        <div class="text-sm font-medium text-gray-500">Total Revenue</div>
        <div class="mt-2 text-3xl font-bold text-gray-900">₱{{ number_format($totalRevenue, 2) }}</div>
        <div class="mt-1 text-sm text-gray-500">All time</div>
    </div>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="text-sm font-medium text-gray-500">Pending Physical Signatures</div>
        <div class="mt-2 text-3xl font-bold text-amber-600">{{ $pendingTreasurer + $pendingMayor }}</div>
        <div class="mt-1 text-sm">
            <span class="text-amber-600">{{ $pendingTreasurer }} Treasurer</span>
            <span class="mx-1 text-gray-400">·</span>
            <span class="text-amber-600">{{ $pendingMayor }} Mayor</span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="text-sm font-medium text-gray-500">Upcoming Burials</div>
        <div class="mt-2 text-3xl font-bold text-gray-900">{{ $upcomingBurials }}</div>
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
        <div class="text-sm font-medium text-gray-500">Pre-Need Plans</div>
        <div class="mt-2 text-3xl font-bold text-gray-900">{{ $activePlans }}</div>
        <div class="mt-1 text-sm">
            <a href="{{ route('pre-need-plans.index') }}" class="text-indigo-600 hover:text-indigo-900">Manage plans</a>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="text-sm font-medium text-gray-500">Available Niches</div>
        <div class="mt-2 text-3xl font-bold text-gray-900">{{ $availableNiches }}</div>
        <div class="mt-1 text-sm">
            <a href="{{ route('columbary-niches.index') }}" class="text-indigo-600 hover:text-indigo-900">Manage</a>
        </div>
    </div>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="text-sm font-medium text-gray-500">Active Contracts</div>
        <div class="mt-2 text-3xl font-bold text-gray-900">{{ $activeContracts }}</div>
        <div class="mt-1 text-sm">
            <a href="{{ route('contracts.index') }}" class="text-indigo-600 hover:text-indigo-900">View</a>
        </div>
    </div>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="text-sm font-medium text-gray-500">Unread Notifications</div>
        <div class="mt-2 text-3xl font-bold text-gray-900">{{ $unreadNotifications }}</div>
        <div class="mt-1 text-sm">
            <a href="{{ route('notifications.index') }}" class="text-indigo-600 hover:text-indigo-900">View</a>
        </div>
    </div>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="text-sm font-medium text-gray-500">Activity Logs</div>
        <div class="mt-2 text-3xl font-bold text-gray-900">{{ $recentActivityLogs->count() }}</div>
        <div class="mt-1 text-sm">
            <a href="{{ route('activity-logs.index') }}" class="text-indigo-600 hover:text-indigo-900">View all</a>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 lg:col-span-2">
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
                    <span class="px-2 py-1 text-xs font-semibold rounded-full @if($burial->burial_status === 'completed') bg-green-100 text-green-800 @elseif($burial->burial_status === 'scheduled') bg-blue-100 text-blue-800 @else bg-gray-100 text-gray-800 @endif">{{ $burial->burial_status }}</span>
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

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-900">Notifications</h3>
            <a href="{{ route('notifications.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">View all</a>
        </div>
        <div class="space-y-3">
            @forelse($notifications as $notification)
                <div class="py-2 border-b border-gray-100 last:border-0 @if(!$notification->is_read) bg-blue-50 -mx-3 px-3 rounded @endif">
                    <div class="flex items-start gap-2">
                        <span class="shrink-0 mt-0.5">
                            @if($notification->type === 'burial_reminder')
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            @elseif(in_array($notification->type, ['installment_due', 'overdue']))
                                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @else
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @endif
                        </span>
                        <div class="min-w-0">
                            <div class="text-sm font-medium text-gray-900 truncate">{{ $notification->title }}</div>
                            <div class="text-xs text-gray-500 truncate">{{ $notification->body }}</div>
                            <div class="text-xs text-gray-400 mt-0.5">{{ $notification->created_at?->diffForHumans() }}</div>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500">No notifications.</p>
            @endforelse
        </div>
    </div>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-900">Recent Activity</h3>
            <a href="{{ route('activity-logs.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">View all</a>
        </div>
        <div class="space-y-3">
            @forelse($recentActivityLogs as $log)
                <div class="py-2 border-b border-gray-100 last:border-0">
                    <div class="flex items-start gap-2">
                        <span class="shrink-0 mt-0.5">
                            @if($log->type === 'burial')
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                            @elseif($log->type === 'payment')
                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @elseif($log->type === 'contract')
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            @elseif($log->type === 'plot')
                                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
                            @else
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @endif
                        </span>
                        <div class="min-w-0">
                            <div class="text-sm text-gray-700">{{ $log->description }}</div>
                            <div class="text-xs text-gray-400">{{ $log->user?->name ?? 'System' }} · {{ $log->created_at?->diffForHumans() }}</div>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500">No activity recorded yet.</p>
            @endforelse
        </div>
    </div>
</div>

@if(($overdueInstallments->count() || $dueInstallments->count()))
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    @if($overdueInstallments->count())
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-red-500">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-red-800">Overdue Installments</h3>
        </div>
        <div class="space-y-3">
            @foreach($overdueInstallments as $installment)
                <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                    <div>
                        <div class="font-medium text-gray-900">{{ $installment->contract?->client?->full_name }}</div>
                        <div class="text-sm text-gray-500">₱{{ number_format($installment->amount_due, 2) }} · due {{ $installment->due_date?->format('M d, Y') }}</div>
                    </div>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Overdue</span>
                </div>
            @endforeach
        </div>
    </div>
    @endif
    @if($dueInstallments->count())
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-amber-500">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-amber-800">Upcoming Installment Due Dates</h3>
        </div>
        <div class="space-y-3">
            @foreach($dueInstallments as $installment)
                <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                    <div>
                        <div class="font-medium text-gray-900">{{ $installment->contract?->client?->full_name }}</div>
                        <div class="text-sm text-gray-500">₱{{ number_format($installment->amount_due, 2) }} · due {{ $installment->due_date?->format('M d, Y') }}</div>
                    </div>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Upcoming</span>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endif
