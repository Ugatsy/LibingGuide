<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\BurialController;
use App\Http\Controllers\BurialSpotController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ColumbaryNicheController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\PlotController;
use App\Http\Controllers\PreNeedPlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicSearchController;
use App\Http\Controllers\PublicBookingController;
use App\Http\Controllers\CemeteryMapController;
use App\Http\Controllers\PathController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
    Route::resource('burial-spots', BurialSpotController::class);
    Route::patch('burial-spots/{burialSpot}/position', [BurialSpotController::class, 'updatePosition'])
         ->name('burial-spots.position');
});

Route::middleware('auth')->group(function () {
    Route::resource('inquiries', InquiryController::class)->except(['edit']);
    Route::resource('plots', PlotController::class);
    Route::patch('plots/{plot}/position', [PlotController::class, 'updatePosition'])->name('plots.position');
    Route::resource('clients', ClientController::class);
    Route::resource('contracts', ContractController::class);
    Route::get('contracts/{contract}/pdf', [ContractController::class, 'pdf'])->name('contracts.pdf');
    Route::resource('payments', PaymentController::class)->except(['edit', 'update']);
    Route::resource('burials', BurialController::class);
    Route::patch('burials/{burial}/approve', [BurialController::class, 'approve'])->name('burials.approve');
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::resource('pre-need-plans', PreNeedPlanController::class);
    Route::resource('columbary-niches', ColumbaryNicheController::class);
    Route::patch('columbary-niches/{columbaryNiche}/position', [ColumbaryNicheController::class, 'updatePosition'])->name('columbary-niches.position');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/mark-read', [NotificationController::class, 'markRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
});

Route::get('/find', [PublicSearchController::class, 'index'])->name('public.find');
Route::get('/find/search', [PublicSearchController::class, 'search'])->name('public.search');
Route::get('/find/markers', [PublicSearchController::class, 'allMarkers'])->name('public.markers');

Route::post('/inquire', [InquiryController::class, 'publicStore'])->name('public.inquire');

Route::get('/plans', [PublicBookingController::class, 'plans'])->name('public.plans');
Route::get('/plans/{preNeedPlan}', [PublicBookingController::class, 'planDetail'])->name('public.plans.detail');
Route::get('/columbarium', [PublicBookingController::class, 'columbarium'])->name('public.columbarium');
Route::get('/lots', [PublicBookingController::class, 'lots'])->name('public.lots');
Route::get('/reserve/{type}', [PublicBookingController::class, 'reserveForm'])->name('public.reserve.form');
Route::post('/reserve', [PublicBookingController::class, 'reserveStore'])->name('public.reserve.store');
Route::get('/reservation-confirmed', [PublicBookingController::class, 'confirmation'])->name('public.reserve.confirmation');

Route::get('/cemetery/polygon', [CemeteryMapController::class, 'getPolygon'])->name('cemetery.polygon.get');

Route::middleware('auth')->group(function () {
    Route::get('/cemetery/admin', [CemeteryMapController::class, 'adminIndex'])->name('cemetery.admin');
    Route::post('/cemetery/polygon', [CemeteryMapController::class, 'savePolygon'])->name('cemetery.polygon.save');
    Route::post('/cemetery/graves', [CemeteryMapController::class, 'saveGrave'])->name('cemetery.graves.save');
    Route::get('/cemetery/graves', [CemeteryMapController::class, 'getGraves'])->name('cemetery.graves.list');
    Route::post('/cemetery/import', [CemeteryMapController::class, 'importGeoJson'])->name('cemetery.import');
    Route::get('/cemetery/seed', [CemeteryMapController::class, 'seedGraves'])->name('cemetery.seed');

    Route::get('/paths', [PathController::class, 'index'])->name('paths.index');
    Route::post('/paths/nodes', [PathController::class, 'storeNode'])->name('paths.nodes.store');
    Route::delete('/paths/nodes/{node}', [PathController::class, 'destroyNode'])->name('paths.nodes.destroy');
    Route::post('/paths/edges', [PathController::class, 'storeEdge'])->name('paths.edges.store');
    Route::delete('/paths/edges/{edge}', [PathController::class, 'destroyEdge'])->name('paths.edges.destroy');
    Route::get('/paths/find', [PathController::class, 'findPath'])->name('paths.find');
    Route::get('/paths/export', [PathController::class, 'export'])->name('paths.export');
    Route::post('/paths/import', [PathController::class, 'import'])->name('paths.import');
    Route::post('/paths/reset', [PathController::class, 'resetAll'])->name('paths.reset');
});

Route::get('/cemetery/find-path', [CemeteryMapController::class, 'findPathToGrave'])->name('cemetery.find-path');

require __DIR__.'/auth.php';
