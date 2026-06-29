<?php

namespace App\Http\Controllers;

use App\Models\BurialPermit;
use App\Models\Contract;
use App\Models\Burial;
use App\Notifications\BurialPermitIssued;
use App\Services\RentalComputationService;
use Illuminate\Http\Request;

class BurialPermitController extends Controller
{
    public function index()
    {
        $permits = BurialPermit::with('contract.client', 'issuedBy')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('burial-permits.index', compact('permits'));
    }

    public function create()
    {
        $contracts = Contract::with('client', 'plot')
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('burial-permits.create', compact('contracts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'contract_id'              => 'required|exists:contracts,id',
            'deceased_name'            => 'required|string|max:255',
            'date_of_birth'            => 'nullable|date',
            'date_of_death'            => 'required|date',
            'death_certificate_number' => 'nullable|string|max:100',
            'burial_permit_fee'        => 'required|numeric|min:0',
            'notes'                    => 'nullable|string|max:1000',
        ]);

        $contract = Contract::findOrFail($validated['contract_id']);

        $count = BurialPermit::count() + 1;
        $validated['permit_number'] = 'AF58-' . str_pad($count, 6, '0', STR_PAD_LEFT);
        $validated['issued_by'] = auth()->id();
        $validated['issued_at'] = now();
        $validated['status'] = 'issued';

        $permit = BurialPermit::create($validated);

        $contract->update(['death_certificate_number' => $validated['death_certificate_number']]);

        if ($contract->client) {
            $contract->client->notify(new BurialPermitIssued($permit));
        }

        return redirect()->route('burial-permits.show', $permit)
            ->with('success', 'Burial Permit (AF 58) issued successfully.');
    }

    public function show(BurialPermit $burialPermit)
    {
        $burialPermit->load('contract.client', 'contract.plot', 'issuedBy');
        return view('burial-permits.show', compact('burialPermit'));
    }

    public function edit(BurialPermit $burialPermit)
    {
        $contracts = Contract::with('client', 'plot')
            ->whereIn('id', [$burialPermit->contract_id])
            ->orWhere('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('burial-permits.edit', compact('burialPermit', 'contracts'));
    }

    public function update(Request $request, BurialPermit $burialPermit)
    {
        $validated = $request->validate([
            'contract_id'              => 'required|exists:contracts,id',
            'deceased_name'            => 'required|string|max:255',
            'date_of_birth'            => 'nullable|date',
            'date_of_death'            => 'required|date',
            'death_certificate_number' => 'nullable|string|max:100',
            'burial_permit_fee'        => 'required|numeric|min:0',
            'status'                   => 'required|in:issued,used,cancelled',
            'notes'                    => 'nullable|string|max:1000',
        ]);

        $burialPermit->update($validated);

        return redirect()->route('burial-permits.show', $burialPermit)
            ->with('success', 'Burial Permit updated.');
    }

    public function destroy(BurialPermit $burialPermit)
    {
        $burialPermit->delete();
        return redirect()->route('burial-permits.index')
            ->with('success', 'Burial Permit deleted.');
    }

    public function computeRental(Request $request, RentalComputationService $service)
    {
        $validated = $request->validate([
            'contract_type'    => 'required|in:new,renewal',
            'ordinance_period' => 'nullable|in:pre_2002,2002_2013,2013_present',
            'lot_type'         => 'required|in:individual,family',
            'area'             => 'nullable|numeric|min:0',
        ]);

        if ($validated['contract_type'] === 'new') {
            return response()->json([
                'type' => 'new',
                'fee' => RentalComputationService::NEW_LOT_FEE,
                'years' => 10,
                'breakdown' => 'New lot fee: ₱' . number_format(RentalComputationService::NEW_LOT_FEE, 2),
            ]);
        }

        $period = $validated['ordinance_period'] ?? '2013_present';

        $result = $service->computeRenewalByOrdinance(
            $period,
            $validated['lot_type'],
            $validated['area'],
            10
        );

        return response()->json($result);
    }
}
