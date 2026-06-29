<?php

namespace App\Http\Controllers;

use App\Models\Plot;
use App\Models\Contract;
use App\Models\Payment;
use App\Models\Burial;
use App\Models\BurialPermit;
use App\Models\Inquiry;
use App\Models\Notification;
use App\Models\ActivityLog;
use App\Models\PreNeedPlan;
use App\Models\ColumbaryNiche;
use App\Models\InstallmentSchedule;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $role = $user->role?->name;

        $totalPlots = Plot::count();
        $occupiedPlots = Plot::whereIn('status', ['occupied', 'full'])->count();
        $availablePlots = Plot::where('status', 'available')->count();
        $reservedPlots = Plot::where('status', 'reserved')->count();
        $occupancyRate = $totalPlots > 0 ? round(($occupiedPlots / $totalPlots) * 100) : 0;

        $totalRevenue = Payment::sum('amount_paid');
        $activeContracts = Contract::where('status', 'active')->count();
        $upcomingBurials = Burial::where('burial_status', 'scheduled')
            ->whereDate('burial_date', '>=', now())->count();
        $issuedPermits = BurialPermit::where('status', 'issued')->count();
        $newInquiries = Inquiry::where('status', 'new')->count();
        $activePlans = PreNeedPlan::where('is_active', true)->count();
        $availableNiches = ColumbaryNiche::where('status', 'available')->count();

        $pendingTreasurer = Contract::whereNotNull('prepared_by')
            ->whereNull('approved_by_treasurer_at')->count();
        $pendingMayor = Contract::whereNotNull('approved_by_treasurer_at')
            ->whereNull('approved_by_mayor_at')->count();

        $recentBurials = Burial::with('plot', 'scheduledBy')
            ->orderBy('burial_date', 'desc')->take(5)->get();
        $recentPayments = Payment::with('contract.client')
            ->orderBy('paid_at', 'desc')->take(5)->get();

        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')->take(10)->get();
        $unreadNotifications = Notification::where('user_id', $user->id)
            ->where('is_read', false)->count();

        $overdueInstallments = InstallmentSchedule::where('status', 'overdue')
            ->whereHas('contract', fn($q) => $q->where('status', 'active'))
            ->with('contract.client')
            ->orderBy('due_date')->take(5)->get();
        $dueInstallments = InstallmentSchedule::whereIn('status', ['unpaid', 'partial'])
            ->whereDate('due_date', '<=', now()->addDays(7))
            ->whereHas('contract', fn($q) => $q->where('status', 'active'))
            ->with('contract.client')
            ->orderBy('due_date')->take(5)->get();

        $data = compact(
            'totalPlots', 'occupiedPlots', 'availablePlots', 'reservedPlots', 'occupancyRate',
            'totalRevenue', 'activeContracts', 'upcomingBurials', 'issuedPermits',
            'newInquiries', 'activePlans', 'availableNiches', 'unreadNotifications',
            'pendingTreasurer', 'pendingMayor',
            'recentBurials', 'recentPayments', 'role',
            'notifications', 'overdueInstallments', 'dueInstallments'
        );

        if ($role === 'super_admin') {
            $data['recentActivityLogs'] = ActivityLog::with('user')
                ->orderBy('created_at', 'desc')->take(10)->get();
        }

        return view('dashboard', $data);
    }
}
