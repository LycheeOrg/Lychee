<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers;

use App\Enum\OauthProvidersType;
use App\Facades\Helpers;
use Dedoc\Scramble\Scramble;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::feeds();

Route::get('/up', function () {
	Event::dispatch(new DiagnosingHealth());

	return view('health-up');
});
Route::get('/octane-health', function () {
	$status = [
		'status' => 'healthy',
		'memory' => memory_get_usage(true) / 1024 / 1024 . ' MB',
		'memory_limit' => ini_get('memory_limit'),
		'db_connected' => DB::connection()->getPdo() !== null,
		'warning' => null,
	];

	// Check for memory pressure
	if (memory_get_usage(true) > 0.8 * Helpers::convertSize(ini_get('memory_limit'))) {
		$status['status'] = 'warning';
		$status['warning'] = 'High memory usage';
	}

	return response()->json($status);
});

Route::get('/', VueController::class)->name('home')->middleware(['migration:complete']);
Route::get('/home', VueController::class)->name('homepage')->middleware(['migration:complete']);
Route::get('/flow/{albumId?}/{photoId?}', [VueController::class, 'gallery'])->name('flow')->middleware(['migration:complete', 'unlock_with_password', 'resolve_album_slug']);
Route::get('/gallery/{albumId?}/{photoId?}', [VueController::class, 'gallery'])->name('gallery')->middleware(['migration:complete', 'unlock_with_password', 'resolve_album_slug']);
Route::get('/frame/{albumId?}', [VueController::class, 'gallery'])->name('frame')->middleware(['migration:complete', 'resolve_album_slug']);
Route::get('/map/{albumId?}', [VueController::class, 'gallery'])->name('map')->middleware(['migration:complete', 'resolve_album_slug']);
Route::get('/search/{albumId?}/{photoId?}', [VueController::class, 'gallery'])->name('search')->middleware(['migration:complete', 'resolve_album_slug']);
Route::get('/timeline/{date?}/{photoId?}', VueController::class)->name('timeline')->middleware(['migration:complete']);
Route::get('/profile', VueController::class)->name('profile')->middleware(['migration:complete', 'login_required:always']);
Route::get('/users', VueController::class)->middleware(['migration:complete', 'login_required:always']);
Route::get('/sharing', VueController::class)->middleware(['migration:complete', 'login_required:always']);
Route::get('/tags', VueController::class)->middleware(['migration:complete', 'login_required:always']);
Route::get('/tag/{tagId}/{photoId?}', VueController::class)->middleware(['migration:complete']);
Route::get('/jobs', VueController::class)->middleware(['migration:complete', 'login_required:always']);
Route::get('/diagnostics', VueController::class)->middleware(['migration:complete']);
Route::get('/statistics', VueController::class)->middleware(['migration:complete', 'login_required:always']);
Route::get('/maintenance', VueController::class)->middleware(['migration:complete', 'login_required:always']);
Route::get('/users', VueController::class)->middleware(['migration:complete', 'login_required:always']);
Route::get('/user-groups', VueController::class)->middleware(['migration:complete', 'login_required:always']);
Route::get('/settings/{tab?}', VueController::class)->middleware(['migration:complete', 'login_required:always']);
Route::get('/permissions', VueController::class)->middleware(['migration:complete', 'login_required:always']);
Route::get('/fixTree', VueController::class)->middleware(['migration:complete', 'login_required:always']);
Route::get('/duplicatesFinder', VueController::class)->middleware(['migration:complete', 'login_required:always']);
Route::get('/changelogs', VueController::class)->middleware(['migration:complete']);
Route::get('/login', VueController::class)->middleware(['migration:complete']);
Route::get('/register', VueController::class)->name('register')->middleware(['migration:complete']);

Route::get('/settings', VueController::class)->middleware(['migration:complete', 'login_required:always']);
Route::get('/permissions', VueController::class)->middleware(['migration:complete', 'login_required:always']);
Route::get('/fixTree', VueController::class)->middleware(['migration:complete', 'login_required:always']);
Route::get('/duplicatesFinder', VueController::class)->middleware(['migration:complete', 'login_required:always']);
Route::get('/renamerRules', VueController::class)->middleware(['migration:complete', 'login_required:always']);
Route::get('/purchasables', VueController::class)->middleware(['migration:complete', 'login_required:always']);
Route::get('/orders', VueController::class)->middleware(['migration:complete', 'login_required:always']);
Route::get('/order/{orderId}/{transactionId?}', VueController::class)->middleware(['migration:complete']);
Route::get('/basket', VueController::class)->middleware(['migration:complete']);
Route::get('/checkout/completed', VueController::class)->middleware(['migration:complete'])->name('shop.checkout.complete');
Route::get('/checkout/failed', VueController::class)->middleware(['migration:complete'])->name('shop.checkout.failed');
Route::get('/checkout/cancelled', VueController::class)->middleware(['migration:complete'])->name('shop.checkout.cancelled');
Route::get('/checkout/{step?}', VueController::class)->middleware(['migration:complete']);

Route::match(['get', 'post'], '/migrate', [Admin\UpdateController::class, 'migrate'])
	->name('migrate')
	->middleware(['migration:incomplete']);

Route::get('/auth/{provider}/redirect', [OauthController::class, 'redirected'])->whereIn('provider', OauthProvidersType::values());
Route::get('/auth/{provider}/authenticate', [OauthController::class, 'authenticate'])->name('oauth-authenticate')->whereIn('provider', OauthProvidersType::values());
Route::get('/auth/{provider}/register', [OauthController::class, 'register'])->name('oauth-register')->whereIn('provider', OauthProvidersType::values());

// We need to register this manually.
Scramble::registerUiRoute(path: 'docs/api')->name('scramble.docs.ui');

Route::match(['get', 'post'], '/api/v1/{path}', fn () => view('error.v1-is-dead'))
	->where('path', '.*')
	->middleware(['migration:complete']);

Route::get('image/{path}', SecurePathController::class)
	->name('image')
	->where('path', '.*');

// This route must be defined last because it is a catch all.
Route::match(['get', 'post'], '{path}', HoneyPotController::class)->where('path', '.*');
