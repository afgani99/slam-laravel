<?php

use App\Http\Controllers\CidController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GamasController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SlaMonthlyController;
use App\Http\Controllers\SlaRestitutionController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketStatusController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::resource('cids', CidController::class)->except('destroy');
    Route::delete('/cids/{cid}', [CidController::class, 'destroy'])->name('cids.destroy');
    Route::resource('tickets', TicketController::class);
    Route::resource('gamas', GamasController::class)->parameters(['gamas' => 'gamas']);
    Route::post('/gamas/{gamas}/close', [GamasController::class, 'close'])->name('gamas.close');
    Route::post('/gamas/{gamas}/pending', [GamasController::class, 'pending'])->name('gamas.pending');
    Route::post('/gamas/{gamas}/resume', [GamasController::class, 'resume'])->name('gamas.resume');
    Route::delete('/gamas/logs/{log}', [GamasController::class, 'deleteLog'])->name('gamas.logs.destroy');
    Route::delete('/gamas/{gamas}/tickets/{ticket}', [GamasController::class, 'removeTicket'])->name('gamas.tickets.destroy');
    Route::post('/tickets/{ticket}/pending', [TicketStatusController::class, 'pending'])->name('tickets.pending');
    Route::post('/tickets/{ticket}/resume', [TicketStatusController::class, 'resume'])->name('tickets.resume');
    Route::post('/tickets/{ticket}/close', [TicketStatusController::class, 'close'])->name('tickets.close');
    Route::delete('/tickets/pending-intervals/{interval}', [App\Http\Controllers\TicketPendingIntervalController::class, 'destroy'])->name('tickets.pending-intervals.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/sla/monthly', SlaMonthlyController::class)->name('sla.monthly');
    Route::get('/sla/restitution', SlaRestitutionController::class)->name('sla.restitution');
    Route::get('/sla/restitution/export', [SlaRestitutionController::class, 'export'])->name('sla.restitution.export');
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::resource('users', UserController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
});

require __DIR__.'/auth.php';
