<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers;

use App\Legacy\V1\Controllers\Administration\UpdateController;
use App\Legacy\V1\Controllers\IndexController;
use App\Legacy\V1\Controllers\RedirectController;
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

// If we are using Livewire by default, we no longer need those routes.
Route::get('/', [IndexController::class, 'show'])->name('home')->middleware(['migration:complete']);
Route::get('/gallery', [IndexController::class, 'gallery'])->name('gallery')->middleware(['migration:complete']);
Route::get('/view', [IndexController::class, 'view'])->name('view')->middleware(['redirect-legacy-id']);
Route::get('/frame', [IndexController::class, 'frame'])->name('frame')->middleware(['migration:complete']);
Route::match(['get', 'post'], '/migrate', [UpdateController::class, 'migrate'])
	->name('migrate')
	->middleware(['migration:incomplete']);

/*
 * TODO see to add better redirection functionality later.
 * This is to prevent instagram from taking control our # in url when sharing an album
 * and not consider it as an hash-tag.
 *
 * Other ideas, redirection by album name, photo title...
 */
Route::get('/r/{albumID}/{photoID}', [RedirectController::class, 'photo'])->middleware(['migration:complete']);
Route::get('/r/{albumID}', [RedirectController::class, 'album'])->middleware(['migration:complete']);

// This route must be defined last because it is a catch all.
Route::match(['get', 'post'], '{path}', HoneyPotController::class)->where('path', '.*');
