<x-app-layout>
    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Contract #{{ $contract->id }}</h1>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <dl class="grid grid-cols-2 gap-4">
                    <div><dt class="text-sm text-gray-600">Client</dt><dd class="font-medium"><a href="{{ route('clients.show', $contract->client) }}" class="text-indigo-600 hover:text-indigo-900">{{ $contract->client->full_name }}</a></dd></div>
                    @if($contract->plot)
                        <div><dt class="text-sm text-gray-600">Plot</dt><dd class="font-medium"><a href="{{ route('plots.show', $contract->plot) }}" class="text-indigo-600 hover:text-indigo-900">{{ $contract->plot->plot_number }}</a></dd></div>
                        <div><dt class="text-sm text-gray-600">Lot Type</dt><dd class="font-medium capitalize">{{ $contract->lot_type ?? 'Individual' }}</dd></div>
                        @if($contract->lot_area)
                            <div><dt class="text-sm text-gray-600">Lot Area</dt><dd class="font-medium">{{ $contract->lot_area }} sqm</dd></div>
                        @endif
                        @if($contract->dimension)
                            <div><dt class="text-sm text-gray-600">Dimension</dt><dd class="font-medium">{{ $contract->dimension }}</dd></div>
                        @endif
                        <div><dt class="text-sm text-gray-600">Lease Type</dt><dd class="font-medium capitalize">{{ $contract->contract_type ?? 'New' }}</dd></div>
                        @if($contract->commencement_date)
                            <div><dt class="text-sm text-gray-600">Commencement</dt><dd class="font-medium">{{ $contract->commencement_date->format('M d, Y') }}</dd></div>
                        @endif
                        @if($contract->expiration_date)
                            <div><dt class="text-sm text-gray-600">Expiration</dt><dd class="font-medium">{{ $contract->expiration_date->format('M d, Y') }}</dd></div>
                        @endif
                        @if($contract->lot_type && $contract->contract_type === 'renewal')
                            <div class="col-span-2">
                                <dt class="text-sm text-gray-600">Rental Breakdown (Renewal)</dt>
                                <dd class="font-medium text-sm">
                                    @if($contract->lot_type === 'family')
                                        {{ $contract->lot_area }} sqm × ₱80/sqm/yr × 10 yrs = <strong>₱{{ number_format(($contract->lot_area ?? 1) * 80 * 10, 2) }}</strong>
                                    @else
                                        ₱200/yr × 10 yrs = <strong>₱2,000.00</strong>
                                    @endif
                                </dd>
                            </div>
                        @endif
                    @endif
                    @if($contract->columbaryNiche)
                        <div><dt class="text-sm text-gray-600">Columbary Niche</dt><dd class="font-medium"><a href="{{ route('columbary-niches.show', $contract->columbaryNiche) }}" class="text-indigo-600 hover:text-indigo-900">{{ $contract->columbaryNiche->niche_number }}</a></dd></div>
                    @endif
                    @if($contract->preNeedPlan)
                        <div><dt class="text-sm text-gray-600">Pre-Need Plan</dt><dd class="font-medium">{{ $contract->preNeedPlan->name }}</dd></div>
                    @endif
                    <div><dt class="text-sm text-gray-600">Contract Date</dt><dd class="font-medium">{{ $contract->contract_date->format('M d, Y') }}</dd></div>
                    <div><dt class="text-sm text-gray-600">Total Amount</dt><dd class="font-medium">₱{{ number_format($contract->total_amount, 2) }}</dd></div>
                    <div><dt class="text-sm text-gray-600">Payment Type</dt><dd class="font-medium">{{ ucfirst(str_replace('_', ' ', $contract->payment_type)) }}</dd></div>
                    <div><dt class="text-sm text-gray-600">Status</dt><dd><span class="px-2 py-1 text-xs font-semibold rounded-full @if($contract->status === 'active') bg-green-100 text-green-800 @elseif($contract->status === 'completed') bg-blue-100 text-blue-800 @else bg-gray-100 text-gray-800 @endif">{{ $contract->status }}</span></dd></div>
                </dl>

                @if($contract->plot)
                    <div class="mt-6 border-t pt-4">
                        <h4 class="font-semibold text-gray-700 mb-2">Signatory Workflow</h4>
                        <div class="flex items-center gap-2 text-sm">
                            <div class="flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full {{ $contract->prepared_by ? 'bg-green-1000' : 'bg-gray-300' }}"></span>
                                <span>Prepared by RCC</span>
                            </div>
                            <span class="text-gray-500">→</span>
                            <div class="flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full {{ $contract->approved_by_treasurer_at ? 'bg-green-1000' : 'bg-gray-300' }}"></span>
                                <span>Treasurer (physical)</span>
                                @if(!$contract->approved_by_treasurer_at && $contract->prepared_by && auth()->user()->hasRole(['rcc_staff', 'super_admin']))
                                    <form method="POST" action="{{ route('contracts.approve-treasurer', $contract) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-900 ml-1">Mark Verified</button>
                                    </form>
                                @endif
                                @if($contract->approved_by_treasurer_at)
                                    <span class="text-xs text-green-600 ml-1">✓ {{ $contract->approved_by_treasurer_at->format('M d, Y') }}</span>
                                @endif
                            </div>
                            <span class="text-gray-500">→</span>
                            <div class="flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full {{ $contract->approved_by_mayor_at ? 'bg-green-1000' : 'bg-gray-300' }}"></span>
                                <span>Mayor (physical)</span>
                                @if(!$contract->approved_by_mayor_at && $contract->approved_by_treasurer_at && auth()->user()->hasRole(['rcc_staff', 'super_admin']))
                                    <form method="POST" action="{{ route('contracts.approve-mayor', $contract) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-900 ml-1">Mark Verified</button>
                                    </form>
                                @endif
                                @if($contract->approved_by_mayor_at)
                                    <span class="text-xs text-green-600 ml-1">✓ {{ $contract->approved_by_mayor_at->format('M d, Y') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 border-t pt-4">
                        <h4 class="font-semibold text-gray-700 mb-2">Official Receipt (AF 51) & Documents</h4>
                        <dl class="grid grid-cols-2 gap-2 text-sm">
                            @if($contract->af_51_number)
                                <div><dt class="text-gray-600">AF 51 / OR #</dt><dd class="font-medium">{{ $contract->af_51_number }}</dd></div>
                            @endif
                            @if($contract->af_51_date)
                                <div><dt class="text-gray-600">OR Date</dt><dd class="font-medium">{{ $contract->af_51_date->format('M d, Y') }}</dd></div>
                            @endif
                            @if($contract->death_certificate_number)
                                <div><dt class="text-gray-600">Death Certificate #</dt><dd class="font-medium">{{ $contract->death_certificate_number }}</dd></div>
                            @endif
                        </dl>
                    </div>
                @endif

                <div class="mt-4 flex gap-4">
                    <a href="{{ route('contracts.edit', $contract) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                    <a href="{{ route('contracts.pdf', $contract) }}" class="text-green-600 hover:text-green-900" target="_blank">Download PDF</a>
                    <form method="POST" action="{{ route('contracts.destroy', $contract) }}" onsubmit="return confirm('Delete this contract?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                    </form>
                </div>
            </div>

            @if($contract->installmentSchedules->count())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Installment Schedule</h3>
                    <table class="w-full text-sm">
                        <thead><tr class="border-b text-left"><th class="py-2">Due Date</th><th>Amount Due</th><th>Amount Paid</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach($contract->installmentSchedules as $is)
                                <tr class="border-b">
                                    <td class="py-2">{{ $is->due_date->format('M d, Y') }}</td>
                                    <td>₱{{ number_format($is->amount_due, 2) }}</td>
                                    <td>₱{{ number_format($is->amount_paid, 2) }}</td>
                                    <td><span class="text-xs px-2 py-1 rounded-full @if($is->status === 'paid') bg-green-100 text-green-800 @elseif($is->status === 'overdue') bg-red-100 text-red-800 @elseif($is->status === 'partial') bg-yellow-100 text-yellow-800 @else bg-gray-100 text-gray-800 @endif">{{ $is->status }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if($contract->payments->count())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Payments</h3>
                    <table class="w-full text-sm">
                        <thead><tr class="border-b text-left"><th class="py-2">Date</th><th>Amount</th><th>Type</th><th>Reference</th><th>Receipt</th></tr></thead>
                        <tbody>
                            @foreach($contract->payments as $payment)
                                <tr class="border-b">
                                    <td class="py-2">{{ $payment->paid_at->format('M d, Y') }}</td>
                                    <td>₱{{ number_format($payment->amount_paid, 2) }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_type)) }}</td>
                                    <td>{{ $payment->reference_number ?? '—' }}</td>
                                    <td>{{ $payment->receipt_number ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if($contract->burialPermits->count())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Burial Permits (AF 58)</h3>
                    @foreach($contract->burialPermits as $permit)
                        <div class="border rounded-lg p-4 mb-2">
                            <div class="flex justify-between">
                                <span class="font-mono font-medium text-sm">{{ $permit->permit_number }}</span>
                                <span class="text-xs px-2 py-1 rounded-full @if($permit->status === 'issued') bg-blue-100 text-blue-800 @elseif($permit->status === 'used') bg-green-100 text-green-800 @else bg-gray-100 text-gray-800 @endif">{{ $permit->status }}</span>
                            </div>
                            <div class="text-sm text-gray-500 mt-1">{{ $permit->deceased_name }} — {{ $permit->issued_at?->format('M d, Y') }}</div>
                            <a href="{{ route('burial-permits.show', $permit) }}" class="text-xs text-indigo-600 hover:text-indigo-900">View Permit</a>
                        </div>
                    @endforeach
                </div>
            @endif

            @php
                $clientNotifications = $contract->client
                    ? \App\Models\SentClientNotification::where('client_id', $contract->client->id)
                        ->where(function($q) use ($contract) {
                            $q->where('reference_type', 'contract')->where('reference_id', $contract->id);
                            $q->orWhere('reference_type', 'burial_permit')->whereIn('reference_id', $contract->burialPermits->pluck('id'));
                            $q->orWhere('reference_type', 'payment')->whereIn('reference_id', $contract->payments->pluck('id'));
                        })
                        ->orderBy('created_at', 'desc')->take(10)->get()
                    : collect();
            @endphp

            @if($clientNotifications->isNotEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Notification History</h3>
                    @foreach($clientNotifications as $cn)
                        <div class="flex items-start gap-3 py-2 border-b border-gray-100 last:border-0">
                            <span class="w-2 h-2 mt-2 rounded-full shrink-0 @if($cn->channel === 'mail') bg-blue-1000 @else bg-gray-500 @endif"></span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800">{{ $cn->subject }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $cn->body }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    Via <span class="capitalize">{{ $cn->channel }}</span>
                                    · {{ $cn->created_at->format('M d, Y g:i A') }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($contract->client)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Send Notification</h3>
                    <form method="POST" action="{{ route('client-notifications.send-manual') }}" class="flex items-center gap-4">
                        @csrf
                        <input type="hidden" name="client_id" value="{{ $contract->client->id }}">
                        <div class="flex-1">
                            <input type="text" name="subject" placeholder="Subject" required class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 mb-2">
                            <textarea name="body" rows="2" placeholder="Message body" required class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>
                        <div class="shrink-0">
                            <select name="channel" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 mb-2">
                                <option value="database">In-App</option>
                                <option value="mail">Email</option>
                            </select>
                            <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm">Send</button>
                        </div>
                    </form>
                    @if($contract->client->email)
                        <p class="text-xs text-gray-500 mt-2">Email: {{ $contract->client->email }}</p>
                    @else
                        <p class="text-xs text-amber-600 mt-2">No email on file — only in-app will work.</p>
                    @endif
                </div>
            @endif

            @if($contract->burials->count())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Burials</h3>
                    @foreach($contract->burials as $burial)
                        <div class="border rounded-lg p-4 mb-2">
                            <div class="flex justify-between">
                                <span class="font-medium">{{ $burial->deceased_name }}</span>
                                <span class="text-xs px-2 py-1 rounded-full @if($burial->burial_status === 'completed') bg-green-100 text-green-800 @else bg-blue-100 text-blue-800 @endif">{{ $burial->burial_status }}</span>
                            </div>
                            <div class="text-sm text-gray-500 mt-1">{{ $burial->burial_date->format('M d, Y g:i A') }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
