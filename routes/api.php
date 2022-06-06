<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

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

Route::prefix('album')->group(function () {
	Route::post('', [AlbumController::class, 'add']);
	Route::post('/tag', [AlbumController::class, 'addTagAlbum']);
	Route::post('/move', [AlbumController::class, 'move']);
	Route::get('/{albumID}', [AlbumController::class, 'get']);
	Route::get('/{albumID}/positions', [AlbumController::class, 'getPositionData']);
	Route::post('/{albumID}/unlock', [AlbumController::class, 'unlock']);
	Route::post('/{albumID}/cover', [AlbumController::class, 'setCover']);
	Route::post('/{albumID}/protect', [AlbumController::class, 'setProtectionPolicy']);
	Route::post('/{albumID}/merge', [AlbumController::class, 'merge']);
	Route::post('/{albumID}/move', [AlbumController::class, 'move']);
	Route::post('/{albumID}/track', [AlbumController::class, 'setTrack'])
		->withoutMiddleware(['content_type:json'])
		->middleware(['content_type:multipart']);
	Route::delete('/{albumID}/track', [AlbumController::class, 'deleteTrack']);
});

Route::prefix('albums')->group(function () {
	Route::get('', [AlbumsController::class, 'get']);
	Route::get('/positions', [AlbumsController::class, 'getPositionData']);
	Route::get('/tree', [AlbumsController::class, 'tree']);
	Route::patch('/tag/{albumIDs}', [AlbumController::class, 'patchTagAlbum']);
	Route::patch('/{albumIDs}', [AlbumController::class, 'patchAlbum']);
	Route::delete('/{albumIDs}', [AlbumController::class, 'delete']);
	Route::get('/{albumIDs}/archive', [AlbumController::class, 'getArchive'])
		->withoutMiddleware(['content_type:json', 'accept_content_type:json'])
		->middleware(['local_storage', 'accept_content_type:any']);
	Route::post('/{albumIDs}/rename', [AlbumController::class, 'setTitle']);
});

Route::get('/frame/settings', [FrameController::class, 'getSettings']);

Route::post('/import/url', [ImportController::class, 'url']);
Route::post('/import/server', [ImportController::class, 'server'])->middleware('admin');
Route::post('/import/server/cancel', [ImportController::class, 'serverCancel'])->middleware('admin');

Route::get('/legacy/translate', [LegacyController::class, 'translateLegacyModelIDs']);

Route::prefix('photo')->group(function () {
	Route::post('', [PhotoController::class, 'add'])
		->withoutMiddleware(['content_type:json'])
		->middleware(['content_type:multipart']);
	Route::post('/clearSymLink', [PhotoController::class, 'clearSymLink']);
	Route::get('/random', [PhotoController::class, 'getRandom']);
	Route::get('/{photoID}', [PhotoController::class, 'get']);
	Route::post('/{photoID}/editor/rotate/{direction}', [PhotoEditorController::class, 'rotate']);
});

Route::prefix('photos')->group(function () {
	Route::patch('/{photoIDs}', [PhotoController::class, 'patchPhoto']);
	Route::delete('/{photoIDs}', [PhotoController::class, 'delete']);
	Route::post('/{photoIDs}/duplicate', [PhotoController::class, 'duplicate']);
	Route::get('/{photoIDs}/archive', [PhotoController::class, 'getArchive'])
		->withoutMiddleware(['content_type:json', 'accept_content_type:json'])
		->middleware(['local_storage', 'accept_content_type:any']);
});

Route::get('/search/{term}', [SearchController::class, 'run']);

Route::get('/session/init', [SessionController::class, 'init']);
Route::post('/session/login', [SessionController::class, 'login']);
Route::post('/session/logout', [SessionController::class, 'logout']);

Route::post('/settings/login', [Administration\SettingsController::class, 'setLogin']);

Route::get('/sharing', [Administration\SharingController::class, 'list']);
Route::post('/sharing', [Administration\SharingController::class, 'add']);
Route::delete('/sharing/{shareIDs}', [Administration\SharingController::class, 'delete']);

Route::prefix('webauthn')->group(function () {
	Route::get('', [Administration\WebAuthController::class, 'list']);
	Route::delete('', [Administration\WebAuthController::class, 'delete']);
	Route::post('/register/gen', [Administration\WebAuthController::class, 'generateRegistration']);
	Route::post('/register', [Administration\WebAuthController::class, 'verifyRegistration']);
	Route::post('/login/gen', [Administration\WebAuthController::class, 'generateAuthentication']);
	Route::post('/login', [Administration\WebAuthController::class, 'verifyAuthentication']);
});
