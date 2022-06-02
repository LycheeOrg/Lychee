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

Route::get('/album/{albumID}', [AlbumController::class, 'get']);
Route::get('/album/{albumID}/positions', [AlbumController::class, 'getPositionData']);
Route::post('/album/{albumID}/unlock', [AlbumController::class, 'unlock']);
Route::post('/album', [AlbumController::class, 'add']);
Route::post('/album/tag', [AlbumController::class, 'addTagAlbum']);
Route::post('/album/{albumID}/nsfw', [AlbumController::class, 'setNSFW']);
Route::post('/album/{albumID}/description', [AlbumController::class, 'setDescription']);
Route::post('/album/{albumID}/cover', [AlbumController::class, 'setCover']);
Route::post('/album/{albumID}/tags', [AlbumController::class, 'setShowTags']);
Route::post('/album/{albumID}/protection', [AlbumController::class, 'setProtectionPolicy']);
Route::post('/album/{albumID}/merge', [AlbumController::class, 'merge']);
Route::post('/album/{albumID}/move', [AlbumController::class, 'move']);
Route::post('/album/{albumID}/license', [AlbumController::class, 'setLicense']);
Route::post('/album/{albumID}/sorting', [AlbumController::class, 'setSorting']);
Route::post('/album/{albumID}/track', [AlbumController::class, 'setTrack'])
	->withoutMiddleware(['content_type:json'])
	->middleware(['content_type:multipart']);
Route::delete('/album/{albumID}/track', [AlbumController::class, 'deleteTrack']);

Route::get('/albums', [AlbumsController::class, 'get']);
Route::get('/albums/positions', [AlbumsController::class, 'getPositionData']);
Route::get('/albums/tree', [AlbumsController::class, 'tree']);
Route::get('/albums/{albumIDs}/archive', [AlbumController::class, 'getArchive'])
	->withoutMiddleware(['content_type:json', 'accept_content_type:json'])
	->middleware(['local_storage', 'accept_content_type:any']);
Route::delete('/albums/{albumIDs}', [AlbumController::class, 'delete']);
Route::post('/albums/{albumIDs}/title', [AlbumController::class, 'setTitle']);

Route::get('/frame/settings', [FrameController::class, 'getSettings']);

Route::post('/import/url', [ImportController::class, 'url']);
Route::post('/import/server', [ImportController::class, 'server'])->middleware('admin');
Route::post('/import/server/cancel', [ImportController::class, 'serverCancel'])->middleware('admin');

Route::get('/legacy/translate', [LegacyController::class, 'translateLegacyModelIDs']);

Route::get('/photo/random', [PhotoController::class, 'getRandom']);
Route::get('/photo/{photoID}', [PhotoController::class, 'get']);
Route::post('/photo/{photoID}/description', [PhotoController::class, 'setDescription']);
Route::post('/photo/{photoID}/public', [PhotoController::class, 'setPublic']);
Route::post('/photo', [PhotoController::class, 'add'])
	->withoutMiddleware(['content_type:json'])
	->middleware(['content_type:multipart']);
Route::post('/photo/{photoID}/license', [PhotoController::class, 'setLicense']);
Route::post('/photo/clearSymLink', [PhotoController::class, 'clearSymLink']);

Route::post('/photo/{photoID}/editor/rotate/{direction}', [PhotoEditorController::class, 'rotate']);

Route::post('/photos/{photoIDs}/title', [PhotoController::class, 'setTitle']);
Route::post('/photos/{photoIDs}/star', [PhotoController::class, 'setStar']);
Route::delete('/photos/{photoIDs}', [PhotoController::class, 'delete']);
Route::post('/photos/{photoIDs}/album', [PhotoController::class, 'setAlbum']);
Route::post('/photos/{photoIDs}/duplicate', [PhotoController::class, 'duplicate']);
Route::get('/photos/{photoIDs}/archive', [PhotoController::class, 'getArchive'])
->withoutMiddleware(['content_type:json', 'accept_content_type:json'])
	->middleware(['local_storage', 'accept_content_type:any']);
Route::post('/photos/{photoIDs}/tags', [PhotoController::class, 'setTags']);

Route::get('/search/{term}', [SearchController::class, 'run']);

Route::get('/session/init', [SessionController::class, 'init']);
Route::post('/session/login', [SessionController::class, 'login']);
Route::post('/session/logout', [SessionController::class, 'logout']);

Route::post('/settings/login', [Administration\SettingsController::class, 'setLogin']);

Route::get('/sharing', [Administration\SharingController::class, 'list']);
Route::post('/sharing', [Administration\SharingController::class, 'add']);
Route::delete('/sharing/{shareIDs}', [Administration\SharingController::class, 'delete']);

Route::post('/webauthn/register/gen', [Administration\WebAuthController::class, 'generateRegistration']);
Route::post('/webauthn/register', [Administration\WebAuthController::class, 'verifyRegistration']);
Route::post('/webauthn/login/gen', [Administration\WebAuthController::class, 'generateAuthentication']);
Route::post('/webauthn/login', [Administration\WebAuthController::class, 'verifyAuthentication']);
Route::get('/webauthn', [Administration\WebAuthController::class, 'list']);
Route::delete('/webauthn', [Administration\WebAuthController::class, 'delete']);
