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
Route::post('/Album::setTitle', [AlbumController::class, 'setTitle']); // TODO uses list
Route::post('/album/{albumID}/nsfw', [AlbumController::class, 'setNSFW']);
Route::post('/album/{albumID}/description', [AlbumController::class, 'setDescription']);
Route::post('/album/{albumID}/cover', [AlbumController::class, 'setCover']);
Route::post('/album/{albumID}/tags', [AlbumController::class, 'setShowTags']);
Route::post('/album/{albumID}/protection', [AlbumController::class, 'setProtectionPolicy']);
Route::delete('/Album::delete', [AlbumController::class, 'delete']); // TODO uses list
Route::post('/Album::merge', [AlbumController::class, 'merge']); // TODO uses list
Route::post('/Album::move', [AlbumController::class, 'move']); // TODO uses list
Route::post('/album/{albumID}/license', [AlbumController::class, 'setLicense']);
Route::post('/album/{albumID}/sorting', [AlbumController::class, 'setSorting']);
Route::get('/Album::getArchive', [AlbumController::class, 'getArchive'])
	->withoutMiddleware(['content_type:json', 'accept_content_type:json'])
	->middleware(['local_storage', 'accept_content_type:any']); // TODO uses list
Route::post('/album/{albumID}/track', [AlbumController::class, 'setTrack'])
	->withoutMiddleware(['content_type:json'])
	->middleware(['content_type:multipart']);
Route::delete('/album/{albumID}/track', [AlbumController::class, 'deleteTrack']);

Route::get('/albums', [AlbumsController::class, 'get']);
Route::get('/albums/positions', [AlbumsController::class, 'getPositionData']);
Route::get('/albums/tree', [AlbumsController::class, 'tree']);

Route::get('/frame/settings', [FrameController::class, 'getSettings']);

Route::post('/Import::url', [ImportController::class, 'url']); // TODO uses list
Route::post('/import/server', [ImportController::class, 'server'])->middleware('admin');
Route::post('/import/server/cancel', [ImportController::class, 'serverCancel'])->middleware('admin');

Route::get('/legacy/translate', [LegacyController::class, 'translateLegacyModelIDs']); // TODO move params to route

Route::get('/photo/random', [PhotoController::class, 'getRandom']);
Route::get('/photo/{photoID}', [PhotoController::class, 'get']);
Route::post('/Photo::setTitle', [PhotoController::class, 'setTitle']); // TODO uses list
Route::post('/photo/{photoID}/description', [PhotoController::class, 'setDescription']);
Route::post('/Photo::setStar', [PhotoController::class, 'setStar']); // TODO uses list
Route::post('/photo/{photoID}/public', [PhotoController::class, 'setPublic']);
Route::post('/Photo::setAlbum', [PhotoController::class, 'setAlbum']); // TODO uses list
Route::post('/Photo::setTags', [PhotoController::class, 'setTags']); // TODO uses list
Route::post('/photo', [PhotoController::class, 'add'])
	->withoutMiddleware(['content_type:json'])
	->middleware(['content_type:multipart']);
Route::delete('/Photo::delete', [PhotoController::class, 'delete']); // TODO uses list
Route::post('/Photo::duplicate', [PhotoController::class, 'duplicate']); // TODO uses list
Route::post('/photo/{photoID}/license', [PhotoController::class, 'setLicense']);
Route::get('/Photo::getArchive', [PhotoController::class, 'getArchive']) // TODO uses list
	->withoutMiddleware(['content_type:json', 'accept_content_type:json'])
	->middleware(['local_storage', 'accept_content_type:any']);
Route::post('/photo/clearSymLink', [PhotoController::class, 'clearSymLink']);

Route::post('/photo/{photoID}/editor/rotate/{direction}', [PhotoEditorController::class, 'rotate']);

Route::get('/search/{term}', [SearchController::class, 'run']);

Route::get('/session/init', [SessionController::class, 'init']);
Route::post('/session/login', [SessionController::class, 'login']);
Route::post('/session/logout', [SessionController::class, 'logout']);

Route::post('/settings/login', [Administration\SettingsController::class, 'setLogin']);

Route::get('/sharing', [Administration\SharingController::class, 'list']);
Route::post('/Sharing::add', [Administration\SharingController::class, 'add']); // TODO uses list
Route::delete('/Sharing::delete', [Administration\SharingController::class, 'delete']); // TODO uses list

Route::post('/webauthn/register/gen', [Administration\WebAuthController::class, 'generateRegistration']);
Route::post('/webauthn/register', [Administration\WebAuthController::class, 'verifyRegistration']);
Route::post('/webauthn/login/gen', [Administration\WebAuthController::class, 'generateAuthentication']);
Route::post('/webauthn/login', [Administration\WebAuthController::class, 'verifyAuthentication']);
Route::get('/webauthn', [Administration\WebAuthController::class, 'list']);
Route::delete('/webauthn', [Administration\WebAuthController::class, 'delete']);
