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
//if (env('APP_ENV') === 'production') {

if (config('app.env') === 'dev') {
	URL::forceScheme('https');
}

Route::feeds();

Route::get('/', [IndexController::class, 'show'])->name('home')->middleware(['installed', 'migrated']);
Route::get('/phpinfo', [IndexController::class, 'phpinfo']);
Route::get('/gallery', [IndexController::class, 'gallery'])->name('gallery')->middleware(['installed', 'migrated']);
Route::get('/migrate', [Administration\UpdateController::class, 'force'])->name('migrate')->middleware('installed');
Route::post('/migrate', [Administration\UpdateController::class, 'force'])->name('migrate')->middleware('installed');

/*
 * TODO see to add better redirection functionality later.
 * This is to prevent instagram from taking control our # in url when sharing an album
 * and not consider it as an hash-tag.
 *
 * Other ideas, redirection by album name, photo title...
 */
Route::get('/r/{albumID}/{photoID}', [RedirectController::class, 'photo'])->middleware(['installed', 'migrated']);
Route::get('/r/{albumID}', [RedirectController::class, 'album'])->middleware(['installed', 'migrated']);

Route::get('/view', [ViewController::class, 'view']);
Route::get('/demo', [DemoController::class, 'js']);
Route::get('/frame', [FrameController::class, 'init'])->name('frame')->middleware(['installed', 'migrated']);

Route::post('/php/index.php', [SessionController::class, 'init']); // entry point if options are not initialized

Route::post('/api/Session::init', [SessionController::class, 'init']);
Route::post('/api/Session::login', [SessionController::class, 'login']);
Route::post('/api/Session::logout', [SessionController::class, 'logout']);

Route::post('/api/webauthn::register/gen', [Administration\WebAuthController::class, 'GenerateRegistration'])->middleware('login');
Route::post('/api/webauthn::register', [Administration\WebAuthController::class, 'VerifyRegistration'])->middleware('login');
Route::post('/api/webauthn::login/gen', [Administration\WebAuthController::class, 'GenerateAuthentication']);
Route::post('/api/webauthn::login', [Administration\WebAuthController::class, 'VerifyAuthentication']);
Route::post('/api/webauthn::list', [Administration\WebAuthController::class, 'List'])->middleware('login');
Route::post('/api/webauthn::delete', [Administration\WebAuthController::class, 'Delete'])->middleware('login');

Route::post('/api/Albums::get', [AlbumsController::class, 'get']);
Route::post('/api/Albums::getPositionData', [AlbumsController::class, 'getPositionData']);
Route::post('/api/Albums::tree', [AlbumsController::class, 'tree']);

Route::post('/api/Album::get', [AlbumController::class, 'get']);
Route::post('/api/Album::getPositionData', [AlbumController::class, 'getPositionData']);
Route::post('/api/Album::unlock', [AlbumController::class, 'unlock']);
Route::post('/api/Album::add', [AlbumController::class, 'add']);
Route::post('/api/Album::addByTags', [AlbumController::class, 'addTagAlbum']);
Route::post('/api/Album::setTitle', [AlbumController::class, 'setTitle']);
Route::post('/api/Album::setNSFW', [AlbumController::class, 'setNSFW']);
Route::post('/api/Album::setDescription', [AlbumController::class, 'setDescription']);
Route::post('/api/Album::setCover', [AlbumController::class, 'setCover']);
Route::post('/api/Album::setShowTags', [AlbumController::class, 'setShowTags']);
Route::post('/api/Album::setPublic', [AlbumController::class, 'setPublic']);
Route::post('/api/Album::delete', [AlbumController::class, 'delete']);
Route::post('/api/Album::merge', [AlbumController::class, 'merge']);
Route::post('/api/Album::move', [AlbumController::class, 'move']);
Route::post('/api/Album::setLicense', [AlbumController::class, 'setLicense']);
Route::post('/api/Album::setSorting', [AlbumController::class, 'setSorting']);
Route::get('/api/Album::getArchive', [AlbumController::class, 'getArchive'])->middleware('local_storage');

Route::post('/api/Frame::getSettings', [FrameController::class, 'getSettings']);

Route::post('/api/Photo::get', [PhotoController::class, 'get']);
Route::post('/api/Photo::getRandom', [PhotoController::class, 'getRandom']);
Route::post('/api/Photo::setTitle', [PhotoController::class, 'setTitle']);
Route::post('/api/Photo::setDescription', [PhotoController::class, 'setDescription']);
Route::post('/api/Photo::setStar', [PhotoController::class, 'setStar']);
Route::post('/api/Photo::setPublic', [PhotoController::class, 'setPublic']);
Route::post('/api/Photo::setAlbum', [PhotoController::class, 'setAlbum']);
Route::post('/api/Photo::setTags', [PhotoController::class, 'setTags']);
Route::post('/api/Photo::add', [PhotoController::class, 'add']);
Route::post('/api/Photo::delete', [PhotoController::class, 'delete']);
Route::post('/api/Photo::duplicate', [PhotoController::class, 'duplicate']);
Route::post('/api/Photo::setLicense', [PhotoController::class, 'setLicense']);
Route::get('/api/Photo::getArchive', [PhotoController::class, 'getArchive'])->middleware('local_storage');
Route::get('/api/Photo::clearSymLink', [PhotoController::class, 'clearSymLink']);

Route::post('/api/PhotoEditor::rotate', [PhotoEditorController::class, 'rotate'])->middleware('upload');

Route::post('/api/Sharing::List', [Administration\SharingController::class, 'listSharing'])->middleware('upload');
Route::post('/api/Sharing::ListUser', [Administration\SharingController::class, 'getUserList'])->middleware('upload');
Route::post('/api/Sharing::Add', [Administration\SharingController::class, 'add'])->middleware('upload');
Route::post('/api/Sharing::Delete', [Administration\SharingController::class, 'delete'])->middleware('upload');

Route::post('/api/Settings::setLogin', [Administration\SettingsController::class, 'setLogin']);

Route::post('/api/Import::url', [ImportController::class, 'url']);
Route::post('/api/Import::server', [ImportController::class, 'server']);
Route::post('/api/Import::serverCancel', [ImportController::class, 'serverCancel']);

Route::post('/api/Diagnostics', [Administration\DiagnosticsController::class, 'get']);
Route::post('/api/Diagnostics::getSize', [Administration\DiagnosticsController::class, 'get_size']);

Route::get('/Diagnostics', [Administration\DiagnosticsController::class, 'show']);

Route::post('/api/search', [SearchController::class, 'search']);

// This route NEEDS to be the last one as it will catch anything else.
Route::get('/{page}', [PageController::class, 'page']);
