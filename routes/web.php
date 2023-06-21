<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

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

if (config('app.force_https')) {
	URL::forceScheme('https');
}

Route::feeds();

Route::get('/', [IndexController::class, 'show'])->name('home')->middleware(['migration:complete']);
Route::get('/gallery', [IndexController::class, 'gallery'])->name('gallery')->middleware(['migration:complete']);
Route::match(['get', 'post'], '/migrate', [Administration\UpdateController::class, 'migrate'])
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

Route::get('/view', [IndexController::class, 'view'])->name('view')->middleware(['redirect-legacy-id']);
Route::get('/frame', [IndexController::class, 'frame'])->name('frame')->middleware(['migration:complete']);

// This route must be defined last because it is a catch all.
Route::match(['get', 'post'], '{path}', HoneyPotController::class)->where('path', '.*');
