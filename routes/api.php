<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// We need that to force https everywhere
// if (env('APP_ENV') === 'production') {

if (config('app.env') === 'dev') {
	URL::forceScheme('https');
}

Route::post('/Album::get', [AlbumController::class, 'get']);
Route::post('/Album::getPositionData', [AlbumController::class, 'getPositionData']);
Route::post('/Album::unlock', [AlbumController::class, 'unlock']);
Route::post('/Album::add', [AlbumController::class, 'add']);
Route::post('/Album::addByTags', [AlbumController::class, 'addTagAlbum']);
Route::post('/Album::setTitle', [AlbumController::class, 'setTitle']);
Route::post('/Album::setNSFW', [AlbumController::class, 'setNSFW']);
Route::post('/Album::setDescription', [AlbumController::class, 'setDescription']);
Route::post('/Album::setCover', [AlbumController::class, 'setCover']);
Route::post('/Album::setShowTags', [AlbumController::class, 'setShowTags']);
Route::post('/Album::setProtectionPolicy', [AlbumController::class, 'setProtectionPolicy']);
Route::post('/Album::delete', [AlbumController::class, 'delete']);
Route::post('/Album::merge', [AlbumController::class, 'merge']);
Route::post('/Album::move', [AlbumController::class, 'move']);
Route::post('/Album::setLicense', [AlbumController::class, 'setLicense']);
Route::post('/Album::setSorting', [AlbumController::class, 'setSorting']);
Route::get('/Album::getArchive', [AlbumController::class, 'getArchive'])
	->withoutMiddleware(['content_type:json', 'accept_content_type:json'])
	->middleware(['local_storage', 'accept_content_type:any']);
Route::post('/Album::setTrack', [AlbumController::class, 'setTrack'])
	->withoutMiddleware(['content_type:json'])
	->middleware(['content_type:multipart']);
Route::post('/Album::deleteTrack', [AlbumController::class, 'deleteTrack']);

Route::post('/Albums::get', [AlbumsController::class, 'get']);
Route::post('/Albums::getPositionData', [AlbumsController::class, 'getPositionData']);
Route::post('/Albums::tree', [AlbumsController::class, 'tree']);

Route::post('/Frame::getSettings', [FrameController::class, 'getSettings']);

Route::post('/Import::url', [ImportController::class, 'url']);
Route::post('/Import::server', [ImportController::class, 'server'])->middleware('admin');
Route::post('/Import::serverCancel', [ImportController::class, 'serverCancel'])->middleware('admin');

Route::post('/Legacy::translateLegacyModelIDs', [LegacyController::class, 'translateLegacyModelIDs']);

Route::post('/Photo::get', [PhotoController::class, 'get']);
Route::post('/Photo::getRandom', [PhotoController::class, 'getRandom']);
Route::post('/Photo::setTitle', [PhotoController::class, 'setTitle']);
Route::post('/Photo::setDescription', [PhotoController::class, 'setDescription']);
Route::post('/Photo::setStar', [PhotoController::class, 'setStar']);
Route::post('/Photo::setPublic', [PhotoController::class, 'setPublic']);
Route::post('/Photo::setAlbum', [PhotoController::class, 'setAlbum']);
Route::post('/Photo::setTags', [PhotoController::class, 'setTags']);
Route::post('/Photo::add', [PhotoController::class, 'add'])
	->withoutMiddleware(['content_type:json'])
	->middleware(['content_type:multipart']);
Route::post('/Photo::delete', [PhotoController::class, 'delete']);
Route::post('/Photo::duplicate', [PhotoController::class, 'duplicate']);
Route::post('/Photo::setLicense', [PhotoController::class, 'setLicense']);
Route::post('/Photo::setUploadDate', [PhotoController::class, 'setUploadDate']);
Route::get('/Photo::getArchive', [PhotoController::class, 'getArchive'])
	->withoutMiddleware(['content_type:json', 'accept_content_type:json'])
	->middleware(['local_storage', 'accept_content_type:any']);
Route::post('/Photo::clearSymLink', [PhotoController::class, 'clearSymLink']);

Route::post('/PhotoEditor::rotate', [PhotoEditorController::class, 'rotate']);

Route::post('/Search::run', [SearchController::class, 'run']);

Route::post('/Session::init', [SessionController::class, 'init']);
Route::post('/Session::login', [SessionController::class, 'login']);
Route::post('/Session::logout', [SessionController::class, 'logout']);

Route::post('/Settings::setLogin', [Administration\SettingsController::class, 'setLogin']);
Route::post('/Settings::updateLogin', [Administration\SettingsController::class, 'updateLogin']);

Route::post('/Sharing::list', [Administration\SharingController::class, 'list']);
Route::post('/Sharing::add', [Administration\SharingController::class, 'add']);
Route::post('/Sharing::setByAlbum', [Administration\SharingController::class, 'setByAlbum']);
Route::post('/Sharing::delete', [Administration\SharingController::class, 'delete']);

// WebAuthn Routes
Route::post('/WebAuthn::list', [WebAuthn\WebAuthnManageController::class, 'list']);
Route::post('/WebAuthn::delete', [WebAuthn\WebAuthnManageController::class, 'delete']);
Route::post('/WebAuthn::register/options', [\App\Http\Controllers\WebAuthn\WebAuthnRegisterController::class, 'options'])
	->name('webauthn.register.options');
Route::post('/WebAuthn::register', [\App\Http\Controllers\WebAuthn\WebAuthnRegisterController::class, 'register'])
	->name('webauthn.register');
Route::post('/WebAuthn::login/options', [\App\Http\Controllers\WebAuthn\WebAuthnLoginController::class, 'options'])
	->name('webauthn.login.options');
Route::post('/WebAuthn::login', [\App\Http\Controllers\WebAuthn\WebAuthnLoginController::class, 'login'])
	->name('webauthn.login');
