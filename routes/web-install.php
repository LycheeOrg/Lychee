<?php

namespace App\Http\Controllers\Install;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

/*
|--------------------------------------------------------------------------
| Install Routes
|--------------------------------------------------------------------------
|
| Here is where you can register install routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "install" middleware group. Now create something great!
|
*/

// We need that to force https everywhere
// if (env('APP_ENV') === 'production') {

if (env('APP_ENV') === 'dev') {
	URL::forceScheme('https');
}

Route::get('install/', [WelcomeController::class, 'view'])->name('install-welcome');
Route::get('install/req', [RequirementsController::class, 'view'])->name('install-req');
Route::get('install/perm', [PermissionsController::class, 'view'])->name('install-perm');
Route::match(['get', 'post'], 'install/env', [EnvController::class, 'view'])->name('install-env');
Route::get('install/migrate', [MigrationController::class, 'view'])->name('install-migrate');

Route::post('install/admin', [SetUpAdminController::class, 'create'])
	->withoutMiddleware(['installation:incomplete'])
	->middleware(['admin_user:unset', 'installation:complete'])
	->name('install-admin');
Route::get('install/admin', [SetUpAdminController::class, 'init'])
	->withoutMiddleware(['installation:incomplete'])
	->middleware(['admin_user:unset', 'installation:complete'])
	->name('install-admin');
