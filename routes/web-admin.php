<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\IndexController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

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

// We need that to force https everywhere
//if (env('APP_ENV') === 'production') {

if (env('APP_ENV') === 'dev') {
	URL::forceScheme('https');
}

Route::get('/phpinfo', [IndexController::class, 'phpinfo']);

Route::get('/Logs', [LogController::class, 'view']);

Route::get('/Diagnostics', [DiagnosticsController::class, 'view']);

Route::get('/Update', [UpdateController::class, 'view']);
