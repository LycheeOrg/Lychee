<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Administration;

use App\Legacy\V1\Controllers\Administration\DiagnosticsController;
use App\Legacy\V1\Controllers\Administration\JobController;
use App\Legacy\V1\Controllers\Administration\OptimizeController;
use App\Legacy\V1\Controllers\Administration\UpdateController;
use App\Legacy\V1\Controllers\IndexController;
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
Route::get('/phpinfo', [IndexController::class, 'phpinfo']);

Route::get('/Jobs', [JobController::class, 'view']);

Route::get('/Permissions', [DiagnosticsController::class, 'getFullAccessPermissions']);

// Traditionally, the diagnostic page has been accessible by anybody
// While this might be helpful for debugging purposes if the setup is so
// broken that even logging in as an administrator fails, it poses a security
// risk.
// TODO: Reconsider, if we really want the diagnostic page to be world-wide accessible.
Route::get('/Diagnostics', [DiagnosticsController::class, 'view']);

Route::get('/Update', [UpdateController::class, 'view'])->name('update');

Route::get('/Optimize', [OptimizeController::class, 'view']);

