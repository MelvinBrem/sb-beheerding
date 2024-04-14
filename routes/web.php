<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/contracts', [DashboardController::class, 'indexContracts'])->name('dashboard-contracts');
Route::get('/sites', [DashboardController::class, 'indexSites'])->name('dashboard-sites');

Route::get('/actions/sync-hubspot-contracts', [DashboardController::class, 'syncHubSpotContracts'])->name('dashboard.sync-hubspot-contracts');
Route::get('/actions/get-hubspot-schemas', [DashboardController::class, 'getHubSpotSchemas'])->name('dashboard.get-hubspot-schemas');
