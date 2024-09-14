<?php

namespace App\Http\Controllers;

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

const MIGRATION_COMPLETE = 'migration:complete';
Route::feeds();

Route::get('/', fn () => view('vueapp'))->name('home')->middleware([MIGRATION_COMPLETE]);
Route::get('/gallery', fn () => view('vueapp'))->name('gallery')->middleware([MIGRATION_COMPLETE]);
Route::get('/gallery/{albumID}', fn () => view('vueapp'))->name('gallery')->middleware([MIGRATION_COMPLETE]);
Route::get('/gallery/{albumID}/{photoID}', fn () => view('vueapp'))->name('gallery')->middleware([MIGRATION_COMPLETE]);
Route::get('/profile', fn () => view('vueapp'))->middleware([MIGRATION_COMPLETE]);
Route::get('/users', fn () => view('vueapp'))->middleware([MIGRATION_COMPLETE]);
Route::get('/sharing', fn () => view('vueapp'))->middleware([MIGRATION_COMPLETE]);
Route::get('/jobs', fn () => view('vueapp'))->middleware([MIGRATION_COMPLETE]);
Route::get('/diagnostics', fn () => view('vueapp'))->middleware([MIGRATION_COMPLETE]);
Route::get('/maintenance', fn () => view('vueapp'))->middleware([MIGRATION_COMPLETE]);
Route::get('/frame', fn () => view('vueapp'))->middleware([MIGRATION_COMPLETE]);
Route::get('/search', fn () => view('vueapp'))->middleware([MIGRATION_COMPLETE]);
Route::get('/map', fn () => view('vueapp'))->middleware([MIGRATION_COMPLETE]);
Route::get('/users', fn () => view('vueapp'))->middleware([MIGRATION_COMPLETE]);
Route::get('/settings', fn () => view('vueapp'))->middleware([MIGRATION_COMPLETE]);
Route::get('/permissions', fn () => view('vueapp'))->middleware([MIGRATION_COMPLETE]);

Route::match(['get', 'post'], '/migrate', [Admin\UpdateController::class, 'migrate'])
	->name('migrate')
	->middleware(['migration:incomplete']);

/*
 * TODO see to add better redirection functionality later.
 * This is to prevent instagram from taking control our # in url when sharing an album
 * and not consider it as an hash-tag.
 *
 * Other ideas, redirection by album name, photo title...
 */
Route::get('/r/{albumID}/{photoID}', [RedirectController::class, 'photo'])->middleware([MIGRATION_COMPLETE]);
Route::get('/r/{albumID}', [RedirectController::class, 'album'])->middleware([MIGRATION_COMPLETE]);

// This route must be defined last because it is a catch all.
Route::match(['get', 'post'], '{path}', HoneyPotController::class)->where('path', '.*');
