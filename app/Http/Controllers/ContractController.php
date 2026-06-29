<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Client;
use App\Models\Plot;
use App\Models\PreNeedPlan;
use App\Models\ColumbaryNiche;
use App\Notifications\ContractApproved;
use App\Services\RentalComputationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    public function index()
    {
        $contracts = Contract::with('client', 'plot', 'preNeedPlan', 'columbaryNiche')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('contracts.index', compact('contracts'));
    }

    public function create()
    {
        $clients = Client::orderBy('full_name')->get();
        $plots = Plot::whereIn('status', ['available', 'reserved'])->orderBy('plot_number')->get();
        $niches = ColumbaryNiche::whereIn('status', ['available', 'reserved'])->orderBy('niche_number')->get();
        $plans = PreNeedPlan::where('is_active', true)->orderBy('name')->get();
        return view('contracts.create', compact('clients', 'plots', 'niches', 'plans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'          => 'required|exists:clients,id',
            'plot_id'            => 'nullable|exists:plots,id',
            'pre_need_plan_id'   => 'nullable|exists:pre_need_plans,id',
            'columbary_niche_id' => 'nullable|exists:columbary_niches,id',
            'contract_date'      => 'required|date',
            'contract_type'      => 'nullable|in:new,renewal',
            'ordinance_period'   => 'nullable|in:pre_2002,2002_2013,2013_present',
            'lot_type'           => 'nullable|in:individual,family',
            'lot_area'           => 'nullable|numeric|min:0',
            'dimension'          => 'nullable|string|max:100',
            'commencement_date'  => 'nullable|date',
            'expiration_date'    => 'nullable|date|after:commencement_date',
            'total_amount'       => 'required|numeric|min:0',
            'payment_type'       => 'required|in:cash,credit_card,installment',
            'status'             => 'nullable|in:active,completed,cancelled',
            'af_51_number'       => 'nullable|string|max:50',
            'af_51_date'         => 'nullable|date',
            'death_certificate_number' => 'nullable|string|max:100',
        ]);

        $validated['prepared_by'] = auth()->id();

        $contract = Contract::create($validated);

        if ($request->payment_type === 'installment') {
            $request->validate(['installments' => 'required|integer|min:2|max:60']);
            $monthly = $validated['total_amount'] / $request->installments;
            for ($i = 1; $i <= $request->installments; $i++) {
                $contract->installmentSchedules()->create([
                    'due_date'  => now()->addMonths($i)->format('Y-m-d'),
                    'amount_due' => round($monthly, 2),
                ]);
            }
        }

        if ($contract->plot_id) $contract->plot()->update(['status' => 'reserved']);
        if ($contract->columbary_niche_id) $contract->columbaryNiche()->update(['status' => 'reserved']);

        return redirect()->route('contracts.index')->with('success', 'Contract created.');
    }

    public function show(Contract $contract)
    {
        $contract->load('client', 'plot', 'preNeedPlan', 'columbaryNiche', 'preparedBy', 'payments', 'installmentSchedules', 'burials', 'burialPermits');
        return view('contracts.show', compact('contract'));
    }

    public function edit(Contract $contract)
    {
        $clients = Client::orderBy('full_name')->get();
        $plots = Plot::orderBy('plot_number')->get();
        $niches = ColumbaryNiche::orderBy('niche_number')->get();
        $plans = PreNeedPlan::where('is_active', true)->orderBy('name')->get();
        return view('contracts.edit', compact('contract', 'clients', 'plots', 'niches', 'plans'));
    }

    public function update(Request $request, Contract $contract)
    {
        $validated = $request->validate([
            'client_id'          => 'required|exists:clients,id',
            'plot_id'            => 'nullable|exists:plots,id',
            'pre_need_plan_id'   => 'nullable|exists:pre_need_plans,id',
            'columbary_niche_id' => 'nullable|exists:columbary_niches,id',
            'contract_date'      => 'required|date',
            'contract_type'      => 'nullable|in:new,renewal',
            'ordinance_period'   => 'nullable|in:pre_2002,2002_2013,2013_present',
            'lot_type'           => 'nullable|in:individual,family',
            'lot_area'           => 'nullable|numeric|min:0',
            'dimension'          => 'nullable|string|max:100',
            'commencement_date'  => 'nullable|date',
            'expiration_date'    => 'nullable|date|after:commencement_date',
            'total_amount'       => 'required|numeric|min:0',
            'payment_type'       => 'required|in:cash,credit_card,installment',
            'status'             => 'nullable|in:active,completed,cancelled',
            'af_51_number'       => 'nullable|string|max:50',
            'af_51_date'         => 'nullable|date',
            'death_certificate_number' => 'nullable|string|max:100',
        ]);

        $contract->update($validated);
        return redirect()->route('contracts.index')->with('success', 'Contract updated.');
    }

    public function destroy(Contract $contract)
    {
        $contract->installmentSchedules()->delete();
        $contract->payments()->delete();
        $contract->delete();
        return redirect()->route('contracts.index')->with('success', 'Contract deleted.');
    }

    public function pdf(Contract $contract)
    {
        $contract->load('client', 'plot', 'preNeedPlan', 'columbaryNiche', 'payments', 'installmentSchedules', 'preparedBy');
        $pdf = Pdf::loadView('contracts.pdf', compact('contract'));
        return $pdf->download("contract-{$contract->id}.pdf");
    }

    public function approveTreasurer(Contract $contract)
    {
        if (!auth()->user()->hasRole(['rcc_staff', 'super_admin'])) {
            abort(403, 'Only RCC or Super Admin can mark Treasurer approval.');
        }

        $contract->update([
            'approved_by_treasurer_at' => now(),
        ]);

        if ($contract->client) {
            $contract->client->notify(new ContractApproved($contract, 'treasurer'));
        }

        return back()->with('success', 'Treasurer signature verified & recorded.');
    }

    public function approveMayor(Contract $contract)
    {
        if (!auth()->user()->hasRole(['rcc_staff', 'super_admin'])) {
            abort(403, 'Only RCC or Super Admin can mark Mayor approval.');
        }

        $contract->update([
            'approved_by_mayor_at' => now(),
        ]);

        if ($contract->client) {
            $contract->client->notify(new ContractApproved($contract, 'mayor'));
        }

        return back()->with('success', 'Mayor signature verified & recorded.');
    }
}
