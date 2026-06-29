<x-app-layout>
    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">New Contract</h1>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('contracts.store') }}" id="contract-form">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Client</label>
                        <select name="client_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select client…</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Product Type</label>
                        <select id="product_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="lot">Lot / Burial Plot</option>
                            <option value="columbary">Columbary Niche</option>
                            <option value="plan">Pre-Need Plan</option>
                        </select>
                    </div>

                    <div id="plot-fields">
                        <div class="mb-4 grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Plot</label>
                                <select name="plot_id" id="plot_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Select plot…</option>
                                    @foreach($plots as $plot)
                                        <option value="{{ $plot->id }}" data-price="{{ $plot->price }}">{{ $plot->plot_number }} ({{ $plot->section ?? 'No section' }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Lot Type</label>
                                <select name="lot_type" id="lot_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="individual">Individual Lot</option>
                                    <option value="family">Family Lot</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-4 grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Lease Type</label>
                                <select name="contract_type" id="lease_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="new">New (₱2,000 — 10 years)</option>
                                    <option value="renewal">Renewal</option>
                                </select>
                            </div>
                            <div id="area-field" style="display:none;">
                                <label class="block text-sm font-medium text-gray-700">Lot Area (sqm)</label>
                                <input type="number" step="0.01" name="lot_area" id="lot_area" value="{{ old('lot_area') }}" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div id="ordinance-field" class="mb-4" style="display:none;">
                            <label class="block text-sm font-medium text-gray-700">Ordinance Period (Renewal Rate)</label>
                            <select name="ordinance_period" id="ordinance_period" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select period…</option>
                                <option value="pre_2002">Before 2002 — Individual ₱20/yr · Family ₱8/sqm/yr</option>
                                <option value="2002_2013">2002 – 2013 — Individual ₱70/yr · Family ₱28/sqm/yr</option>
                                <option value="2013_present">2013 – Present — Individual ₱200/yr · Family ₱80/sqm/yr</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Select the ordinance period that applies to this renewal.</p>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Dimension / Location</label>
                            <input type="text" name="dimension" value="{{ old('dimension') }}" placeholder="e.g. 2m × 3m, Section A, Row 5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="mb-4 grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Commencement Date</label>
                                <input type="date" name="commencement_date" value="{{ old('commencement_date', date('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Expiration Date</label>
                                <input type="date" name="expiration_date" value="{{ old('expiration_date') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                        <div class="mb-4 p-4 bg-gray-50 rounded-lg hidden" id="rental-computation">
                            <h4 class="font-medium text-sm text-gray-700 mb-2">Rental Fee Computation</h4>
                            <div id="computation-result" class="text-sm"></div>
                            <button type="button" id="apply-rental" class="mt-2 text-xs bg-indigo-100 text-indigo-700 px-3 py-1 rounded hover:bg-indigo-200 hidden">Apply to Total Amount</button>
                        </div>
                    </div>

                    <div class="mb-4" id="columbary-field" style="display:none;">
                        <label class="block text-sm font-medium text-gray-700">Columbary Niche</label>
                        <select name="columbary_niche_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select niche…</option>
                            @foreach($niches ?? [] as $niche)
                                <option value="{{ $niche->id }}">{{ $niche->niche_number }} ({{ $niche->section ?? 'No section' }} — ₱{{ number_format($niche->price, 2) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4" id="plan-field" style="display:none;">
                        <label class="block text-sm font-medium text-gray-700">Pre-Need Plan</label>
                        <select name="pre_need_plan_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select plan…</option>
                            @foreach($plans ?? [] as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }} ({{ ucfirst($plan->type) }} — ₱{{ number_format($plan->price, 2) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Contract Date</label>
                        <input type="date" name="contract_date" value="{{ old('contract_date', date('Y-m-d')) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Total Amount (₱)</label>
                        <input type="number" step="0.01" name="total_amount" id="total_amount" value="{{ old('total_amount') }}" min="0" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Payment Type</label>
                        <select name="payment_type" id="payment_type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="cash">Cash</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="installment">Installment</option>
                        </select>
                    </div>
                    <div class="mb-4" id="installment-fields" style="display:none;">
                        <label class="block text-sm font-medium text-gray-700">Number of Installments (months)</label>
                        <input type="number" name="installments" value="6" min="2" max="60" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <hr class="my-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Official Receipt (AF 51) & Document References</h3>
                    <div class="mb-4 grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">AF 51 / Official Receipt #</label>
                            <input type="text" name="af_51_number" value="{{ old('af_51_number') }}" placeholder="e.g. 123456" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">AF 51 Date</label>
                            <input type="date" name="af_51_date" value="{{ old('af_51_date', date('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Death Certificate Number</label>
                        <input type="text" name="death_certificate_number" value="{{ old('death_certificate_number') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="flex items-center justify-end gap-4">
                        <a href="{{ route('contracts.index') }}" class="text-gray-600 hover:text-gray-900">Cancel</a>
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Create Contract</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        function toggleProductType() {
            const t = document.getElementById('product_type').value;
            document.getElementById('plot-fields').style.display = t === 'lot' ? 'block' : 'none';
            document.getElementById('columbary-field').style.display = t === 'columbary' ? 'block' : 'none';
            document.getElementById('plan-field').style.display = t === 'plan' ? 'block' : 'none';
        }
        document.getElementById('product_type')?.addEventListener('change', toggleProductType);
        document.getElementById('payment_type')?.addEventListener('change', function() {
            document.getElementById('installment-fields').style.display = this.value === 'installment' ? 'block' : 'none';
        });

        function toggleLeaseFields() {
            const lotType = document.getElementById('lot_type')?.value;
            const areaField = document.getElementById('area-field');
            areaField.style.display = lotType === 'family' ? 'block' : 'none';
            if (lotType !== 'family') {
                document.getElementById('lot_area').value = '';
            }
            computeRental();
        }
        document.getElementById('lot_type')?.addEventListener('change', toggleLeaseFields);

        function toggleOrdinanceField() {
            const leaseType = document.getElementById('lease_type')?.value;
            const field = document.getElementById('ordinance-field');
            field.style.display = leaseType === 'renewal' ? 'block' : 'none';
            if (leaseType !== 'renewal') {
                document.getElementById('ordinance_period').value = '';
            }
            computeRental();
        }
        document.getElementById('lease_type')?.addEventListener('change', toggleOrdinanceField);
        document.getElementById('ordinance_period')?.addEventListener('change', computeRental);
        document.getElementById('lot_area')?.addEventListener('change', computeRental);
        document.getElementById('lot_area')?.addEventListener('input', computeRental);

        function computeRental() {
            const leaseType = document.getElementById('lease_type')?.value;
            const lotType = document.getElementById('lot_type')?.value;
            const area = parseFloat(document.getElementById('lot_area')?.value) || 0;
            const ordinancePeriod = document.getElementById('ordinance_period')?.value;

            const resultEl = document.getElementById('rental-computation');
            const resultDiv = document.getElementById('computation-result');
            const applyBtn = document.getElementById('apply-rental');

            if (leaseType === 'new') {
                resultEl.style.display = 'block';
                resultDiv.innerHTML = '<div class="text-green-700 font-medium">New Lot — Fixed Rate</div>' +
                    '<div class="mt-1">₱2,000.00 for 10 years (renewable)</div>' +
                    '<div class="mt-2 font-semibold text-green-800">Total: ₱2,000.00</div>';
                applyBtn.classList.remove('hidden');
                applyBtn.dataset.amount = '2000';
                return;
            }

            if (leaseType === 'renewal' && !ordinancePeriod) {
                resultEl.style.display = 'none';
                return;
            }

            if (leaseType === 'renewal' && lotType === 'family' && !area) {
                resultEl.style.display = 'block';
                resultDiv.innerHTML = '<div class="text-amber-600">Please enter the lot area (sqm) to compute the family lot renewal rate.</div>';
                applyBtn.classList.add('hidden');
                return;
            }

            fetch('{{ route("burial-permits.compute-rental") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({
                    contract_type: leaseType,
                    ordinance_period: ordinancePeriod,
                    lot_type: lotType,
                    area: area
                })
            })
            .then(r => r.json())
            .then(data => {
                resultEl.style.display = 'block';

                if (data.type === 'new') {
                    resultDiv.innerHTML = '<div class="text-green-700 font-medium">New Lot — Fixed Rate</div>' +
                        '<div class="mt-1">' + data.breakdown + '</div>';
                    applyBtn.classList.remove('hidden');
                    applyBtn.dataset.amount = data.fee;
                } else if (data.type === 'renewal') {
                    let periodLabel = '';
                    if (data.ordinance_period === 'pre_2002') periodLabel = 'Before 2002';
                    else if (data.ordinance_period === '2002_2013') periodLabel = '2002 – 2013';
                    else periodLabel = '2013 – Present';

                    resultDiv.innerHTML = '<div class="text-green-700 font-medium">Renewal — ' + periodLabel + ' Rate</div>' +
                        '<div class="mt-1">' + data.breakdown + '</div>' +
                        '<div class="mt-2 font-semibold text-green-800">Total for ' + data.years + ' years: ₱' + Number(data.fee).toLocaleString(undefined, {minimumFractionDigits: 2}) + '</div>' +
                        '<div class="text-xs text-gray-500 mt-1">Annual rate: ₱' + Number(data.annual_rate).toLocaleString(undefined, {minimumFractionDigits: 2}) + (lotType === 'family' ? '/sqm' : '') + '/yr</div>';
                    applyBtn.classList.remove('hidden');
                    applyBtn.dataset.amount = data.fee;
                }
            })
            .catch(() => {
                resultEl.style.display = 'block';
                resultDiv.innerHTML = '<div class="text-red-600">Error computing rental fee. Please try again.</div>';
                applyBtn.classList.add('hidden');
            });
        }

        document.getElementById('apply-rental')?.addEventListener('click', function() {
            const amount = this.dataset.amount;
            if (amount) {
                document.getElementById('total_amount').value = amount;
                this.textContent = '✓ Applied';
                this.classList.remove('bg-indigo-100', 'text-indigo-700', 'hover:bg-indigo-200');
                this.classList.add('bg-green-100', 'text-green-700');
            }
        });

        toggleProductType();
        toggleLeaseFields();
    </script>
</x-app-layout>