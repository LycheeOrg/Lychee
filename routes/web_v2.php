<?php

namespace App\Http\Controllers;

use App\Enum\OauthProvidersType;
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

Route::get('/', fn () => view('vueapp'))->name('home')->middleware(['migration:complete']);
Route::get('/gallery', fn () => view('vueapp'))->name('gallery')->middleware(['migration:complete']);
Route::get('/gallery/{albumID}', fn () => view('vueapp'))->name('gallery')->middleware(['migration:complete']);
Route::get('/gallery/{albumID}/{photoID}', fn () => view('vueapp'))->name('gallery')->middleware(['migration:complete']);

Route::get('/frame', fn () => view('vueapp'))->name('frame')->middleware(['migration:complete']);
Route::get('/frame/{albumID}', fn () => view('vueapp'))->name('frame')->middleware(['migration:complete']);

Route::get('/map', fn () => view('vueapp'))->name('map')->middleware(['migration:complete']);
Route::get('/map/{albumID}', fn () => view('vueapp'))->name('map')->middleware(['migration:complete']);

// later
Route::get('/search', fn () => view('vueapp'))->middleware(['migration:complete']);

Route::get('/profile', fn () => view('vueapp'))->name('profile')->middleware(['migration:complete']);
Route::get('/users', fn () => view('vueapp'))->middleware(['migration:complete']);
Route::get('/sharing', fn () => view('vueapp'))->middleware(['migration:complete']);
Route::get('/jobs', fn () => view('vueapp'))->middleware(['migration:complete']);
Route::get('/diagnostics', fn () => view('vueapp'))->middleware(['migration:complete']);
Route::get('/maintenance', fn () => view('vueapp'))->middleware(['migration:complete']);
Route::get('/users', fn () => view('vueapp'))->middleware(['migration:complete']);
Route::get('/settings', fn () => view('vueapp'))->middleware(['migration:complete']);
Route::get('/permissions', fn () => view('vueapp'))->middleware(['migration:complete']);

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

// This route must be defined last because it is a catch all.
Route::match(['get', 'post'], '{path}', HoneyPotController::class)->where('path', '.*');
