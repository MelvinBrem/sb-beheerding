<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/actions/sync-hubspot-contracts', [DashboardController::class, 'syncHubSpotContracts'])->name('dashboard.sync-hubspot-contracts');
Route::get('/actions/get-hubspot-schemas', [DashboardController::class, 'getHubSpotSchemas'])->name('dashboard.get-hubspot-schemas');
Route::get('/actions/sync-uptimerobot', [DashboardController::class, 'syncUptimeRobot'])->name('dashboard.sync-uptimerobot');
Route::get('/actions/sync-managewp', [DashboardController::class, 'syncManageWP'])->name('dashboard.sync-managewp');
