<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers;

use App\Enum\OauthProvidersType;
use App\Legacy\V1\Controllers\RedirectController;
use Dedoc\Scramble\Scramble;
use Illuminate\Foundation\Events\DiagnosingHealth;
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

Route::get('/', [VueController::class, 'view'])->name('home')->middleware(['migration:complete']);
Route::get('/gallery', [VueController::class, 'view'])->name('gallery')->middleware(['migration:complete']);
Route::get('/gallery/{albumId}', [VueController::class, 'view'])->name('gallery-album')->middleware(['migration:complete', 'unlock_with_password']);
Route::get('/gallery/{albumId}/{photoId}', [VueController::class, 'view'])->name('gallery-photo')->middleware(['migration:complete', 'unlock_with_password']);

Route::get('/frame', [VueController::class, 'view'])->name('frame')->middleware(['migration:complete']);
Route::get('/frame/{albumId}', [VueController::class, 'view'])->name('frame-album')->middleware(['migration:complete']);

Route::get('/map', [VueController::class, 'view'])->name('map')->middleware(['migration:complete']);
Route::get('/map/{albumId}', [VueController::class, 'view'])->name('map-album')->middleware(['migration:complete']);

// later
Route::get('/search', [VueController::class, 'view'])->name('search')->middleware(['migration:complete']);
Route::get('/search/{albumId}', [VueController::class, 'view'])->name('search-album')->middleware(['migration:complete']);
Route::get('/search/{albumId}/{photoId}', [VueController::class, 'view'])->name('search-photo')->middleware(['migration:complete']);

Route::get('/profile', [VueController::class, 'view'])->name('profile')->middleware(['migration:complete', 'login_required:always']);
Route::get('/users', [VueController::class, 'view'])->middleware(['migration:complete', 'login_required:always']);
Route::get('/sharing', [VueController::class, 'view'])->middleware(['migration:complete', 'login_required:always']);
Route::get('/jobs', [VueController::class, 'view'])->middleware(['migration:complete', 'login_required:always']);
Route::get('/diagnostics', [VueController::class, 'view'])->middleware(['migration:complete']);
Route::get('/statistics', [VueController::class, 'view'])->middleware(['migration:complete', 'login_required:always']);
Route::get('/maintenance', [VueController::class, 'view'])->middleware(['migration:complete', 'login_required:always']);
Route::get('/users', [VueController::class, 'view'])->middleware(['migration:complete', 'login_required:always']);
Route::get('/settings', [VueController::class, 'view'])->middleware(['migration:complete', 'login_required:always']);
Route::get('/permissions', [VueController::class, 'view'])->middleware(['migration:complete', 'login_required:always']);
Route::get('/fixTree', [VueController::class, 'view'])->middleware(['migration:complete', 'login_required:always']);
Route::get('/duplicatesFinder', [VueController::class, 'view'])->middleware(['migration:complete', 'login_required:always']);

Route::match(['get', 'post'], '/migrate', [Admin\UpdateController::class, 'migrate'])
	->name('migrate')
	->middleware(['migration:incomplete']);

Route::get('/auth/{provider}/redirect', [OauthController::class, 'redirected'])->whereIn('provider', OauthProvidersType::values());
Route::get('/auth/{provider}/authenticate', [OauthController::class, 'authenticate'])->name('oauth-authenticate')->whereIn('provider', OauthProvidersType::values());
Route::get('/auth/{provider}/register', [OauthController::class, 'register'])->name('oauth-register')->whereIn('provider', OauthProvidersType::values());

/*
 * TODO see to add better redirection functionality later.
 * This is to prevent instagram from taking control our # in url when sharing an album
 * and not consider it as an hash-tag.
 *
 * Other ideas, redirection by album name, photo title...
 */
Route::get('/r/{albumID}/{photoID}', [RedirectController::class, 'photo'])->middleware(['migration:complete']);
Route::get('/r/{albumID}', [RedirectController::class, 'album'])->middleware(['migration:complete']);

// We need to register this manually.
Scramble::registerUiRoute(path: 'docs/api')->name('scramble.docs.ui');

// This route must be defined last because it is a catch all.
Route::match(['get', 'post'], '{path}', HoneyPotController::class)->where('path', '.*');
