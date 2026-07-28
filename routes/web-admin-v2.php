<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "admin" middleware group. Now create something great!
|
*/
Route::get('/phpinfo', [Admin\DiagnosticsController::class, 'phpinfo']);
Route::get('/Update', [Admin\UpdateController::class, 'view'])->name('update');

// Memory Profiler (Feature 053): owner-only, feature-flagged Blade admin
// surface. This file is registered before routes/web_v2.php in
// RouteServiceProvider::boot(), so these explicit routes are matched ahead
// of the Vue SPA's `/admin` catch-all.
Route::prefix('admin')->middleware(['login_required:always', 'feature:memory-profiler', 'owner'])->group(function (): void {
	Route::get('profiler', [Admin\ProfilerController::class, 'index'])->name('admin.profiler.index');
	Route::get('profiler/{trace}/svg', [Admin\ProfilerController::class, 'svg'])->name('admin.profiler.svg');
	Route::get('profiler/{trace}/download', [Admin\ProfilerController::class, 'download'])->name('admin.profiler.download');
	Route::post('profiler/prune', [Admin\ProfilerController::class, 'prune'])->name('admin.profiler.prune');
});

