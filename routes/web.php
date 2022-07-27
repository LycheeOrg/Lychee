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

// We need that to force https everywhere
// if (env('APP_ENV') === 'production') {

if (config('app.env') === 'dev') {
	URL::forceScheme('https');
}

Route::feeds();

Route::get('/', [IndexController::class, 'show'])->name('home')->middleware(['installation:complete', 'migration:complete']);
Route::get('/gallery', [IndexController::class, 'gallery'])->name('gallery')->middleware(['installation:complete', 'migration:complete']);
Route::match(['get', 'post'], '/migrate', [Administration\UpdateController::class, 'migrate'])
	->name('migrate')
	->middleware(['installation:complete', 'migration:incomplete']);

/*
 * TODO see to add better redirection functionality later.
 * This is to prevent instagram from taking control our # in url when sharing an album
 * and not consider it as an hash-tag.
 *
 * Other ideas, redirection by album name, photo title...
 */
Route::get('/r/{albumID}/{photoID}', [RedirectController::class, 'photo'])->middleware(['installation:complete', 'migration:complete']);
Route::get('/r/{albumID}', [RedirectController::class, 'album'])->middleware(['installation:complete', 'migration:complete']);

Route::get('/view', [ViewController::class, 'view'])->name('view')->middleware(['redirect-legacy-id']);
Route::get('/demo', [DemoController::class, 'js']);
Route::get('/frame', [FrameController::class, 'init'])->name('frame')->middleware(['installation:complete', 'migration:complete']);

// This route NEEDS to be the last one as it will catch anything else.
Route::get('/{page}', [PageController::class, 'page']);
