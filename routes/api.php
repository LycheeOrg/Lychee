<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\Internal\NotImplementedException;
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

/**
 * ALBUMS.
 */
Route::post('/Albums::get', [AlbumsController::class, 'get'])->middleware(['login_required']);
Route::post('/Albums::getPositionData', [AlbumsController::class, 'getPositionData'])->middleware(['login_required']);
Route::post('/Albums::tree', [AlbumsController::class, 'tree'])->middleware(['login_required']);

/**
 * ALBUM.
 */
Route::post('/Album::get', [AlbumController::class, 'get'])->middleware(['login_required']);
Route::post('/Album::getPositionData', [AlbumController::class, 'getPositionData']);
Route::post('/Album::unlock', [AlbumController::class, 'unlock']);
Route::post('/Album::add', [AlbumController::class, 'add']);
Route::post('/Album::addByTags', [AlbumController::class, 'addTagAlbum']);
Route::post('/Album::setTitle', [AlbumController::class, 'setTitle']);
Route::post('/Album::setNSFW', [AlbumController::class, 'setNSFW']);
Route::post('/Album::setDescription', [AlbumController::class, 'setDescription']);
Route::post('/Album::setCopyright', [AlbumController::class, 'setCopyright']);
Route::post('/Album::setCover', [AlbumController::class, 'setCover']);
Route::post('/Album::setHeader', [AlbumController::class, 'setHeader']);
Route::post('/Album::setShowTags', [AlbumController::class, 'setShowTags']);
Route::post('/Album::setProtectionPolicy', [AlbumController::class, 'setProtectionPolicy']);
Route::post('/Album::delete', [AlbumController::class, 'delete']);
Route::post('/Album::merge', [AlbumController::class, 'merge']);
Route::post('/Album::move', [AlbumController::class, 'move']);
Route::post('/Album::setLicense', [AlbumController::class, 'setLicense']);
Route::post('/Album::setSorting', [AlbumController::class, 'setSorting']);
Route::get('/Album::getArchive', [AlbumController::class, 'getArchive'])
	->name('download')
	->withoutMiddleware(['content_type:json', 'accept_content_type:json'])
	->middleware(['accept_content_type:any']);
Route::post('/Album::setTrack', [AlbumController::class, 'setTrack'])
	->withoutMiddleware(['content_type:json'])
	->middleware(['content_type:multipart']);
Route::post('/Album::deleteTrack', [AlbumController::class, 'deleteTrack']);

/**
 * IMPORT.
 */
Route::post('/Import::url', [ImportController::class, 'url']);
Route::post('/Import::server', [ImportController::class, 'server']);
Route::post('/Import::serverCancel', [ImportController::class, 'serverCancel']);

/**
 * LEGACY.
 */
Route::post('/Legacy::translateLegacyModelIDs', [LegacyController::class, 'translateLegacyModelIDs']);

/**
 * PHOTO.
 */
Route::post('/Photo::get', [PhotoController::class, 'get'])->middleware(['login_required']);
Route::post('/Photo::getRandom', [PhotoController::class, 'getRandom'])->middleware(['login_required']);
Route::post('/Photo::setTitle', [PhotoController::class, 'setTitle']);
Route::post('/Photo::setDescription', [PhotoController::class, 'setDescription']);
Route::post('/Photo::setStar', [PhotoController::class, 'setStar']);
Route::post('/Photo::setPublic', fn () => throw new NotImplementedException('This code is deprecated. Good bye.')); // just legacy stuff.
Route::post('/Photo::setAlbum', [PhotoController::class, 'setAlbum']);
Route::post('/Photo::setTags', [PhotoController::class, 'setTags']);
Route::post('/Photo::delete', [PhotoController::class, 'delete']);
Route::post('/Photo::duplicate', [PhotoController::class, 'duplicate']);
Route::post('/Photo::setLicense', [PhotoController::class, 'setLicense']);
Route::post('/Photo::setUploadDate', [PhotoController::class, 'setUploadDate']);
Route::post('/Photo::clearSymLink', [PhotoController::class, 'clearSymLink']);
Route::post('/PhotoEditor::rotate', [PhotoEditorController::class, 'rotate']);
Route::post('/Photo::add', [PhotoController::class, 'add'])
	->withoutMiddleware(['content_type:json'])
	->middleware(['content_type:multipart']);
Route::get('/Photo::getArchive', [PhotoController::class, 'getArchive'])
	->name('photo_download')
	->withoutMiddleware(['content_type:json', 'accept_content_type:json'])
	->middleware(['accept_content_type:any']);

/**
 * SEARCH.
 */
Route::post('/Search::run', [SearchController::class, 'run']);

/**
 * SESSION.
 */
Route::post('/Session::init', [SessionController::class, 'init']);
Route::post('/Session::login', [SessionController::class, 'login']);
Route::post('/Session::logout', [SessionController::class, 'logout']);

/**
 * USER.
 */
Route::post('/User::updateLogin', [Administration\UserController::class, 'updateLogin']);
Route::post('/User::setEmail', [Administration\UserController::class, 'setEmail']);
Route::post('/User::getAuthenticatedUser', [Administration\UserController::class, 'getAuthenticatedUser']);
Route::post('/User::resetToken', [Administration\UserController::class, 'resetToken']);
Route::post('/User::unsetToken', [Administration\UserController::class, 'unsetToken']);

/**
 * USERS.
 */
Route::post('/Users::list', [Administration\UsersController::class, 'list']);
Route::post('/Users::save', [Administration\UsersController::class, 'save']);
Route::post('/Users::delete', [Administration\UsersController::class, 'delete']);
Route::post('/Users::create', [Administration\UsersController::class, 'create']);

/**
 * WEBAUTHN.
 */
Route::post('/WebAuthn::list', [WebAuthn\WebAuthnManageController::class, 'list']);
Route::post('/WebAuthn::delete', [WebAuthn\WebAuthnManageController::class, 'delete']);
Route::post('/WebAuthn::register/options', [WebAuthn\WebAuthnRegisterController::class, 'options'])
	->name('webauthn.register.options');
Route::post('/WebAuthn::register', [WebAuthn\WebAuthnRegisterController::class, 'register'])
	->name('webauthn.register');
Route::post('/WebAuthn::login/options', [WebAuthn\WebAuthnLoginController::class, 'options'])
	->name('webauthn.login.options');
Route::post('/WebAuthn::login', [WebAuthn\WebAuthnLoginController::class, 'login'])
	->name('webauthn.login');

/**
 * SHARING.
 */
Route::post('/Sharing::list', [Administration\SharingController::class, 'list']);
Route::post('/Sharing::add', [Administration\SharingController::class, 'add']);
Route::post('/Sharing::setByAlbum', [Administration\SharingController::class, 'setByAlbum']);
Route::post('/Sharing::delete', [Administration\SharingController::class, 'delete']);

/**
 * DIAGNOSTICS.
 */
Route::post('/Diagnostics::get', [Administration\DiagnosticsController::class, 'get']);
Route::post('/Diagnostics::getSize', [Administration\DiagnosticsController::class, 'getSize']);

/**
 * SETTINGS.
 */
Route::post('/Settings::setSorting', [Administration\SettingsController::class, 'setSorting']);
Route::post('/Settings::setLang', [Administration\SettingsController::class, 'setLang']);
Route::post('/Settings::setLayout', [Administration\SettingsController::class, 'setLayout']);
Route::post('/Settings::setPublicSearch', [Administration\SettingsController::class, 'setPublicSearch']);
Route::post('/Settings::setDefaultLicense', [Administration\SettingsController::class, 'setDefaultLicense']);
Route::post('/Settings::setMapDisplay', [Administration\SettingsController::class, 'setMapDisplay']);
Route::post('/Settings::setMapDisplayPublic', [Administration\SettingsController::class, 'setMapDisplayPublic']);
Route::post('/Settings::setMapProvider', [Administration\SettingsController::class, 'setMapProvider']);
Route::post('/Settings::setMapIncludeSubAlbums', [Administration\SettingsController::class, 'setMapIncludeSubAlbums']);
Route::post('/Settings::setLocationDecoding', [Administration\SettingsController::class, 'setLocationDecoding']);
Route::post('/Settings::setLocationShow', [Administration\SettingsController::class, 'setLocationShow']);
Route::post('/Settings::setLocationShowPublic', [Administration\SettingsController::class, 'setLocationShowPublic']);
Route::post('/Settings::setCSS', [Administration\SettingsController::class, 'setCSS']);
Route::post('/Settings::setJS', [Administration\SettingsController::class, 'setJS']);
Route::post('/Settings::getAll', [Administration\SettingsController::class, 'getAll']);
Route::post('/Settings::saveAll', [Administration\SettingsController::class, 'saveAll']);
Route::post('/Settings::setAlbumDecoration', [Administration\SettingsController::class, 'setAlbumDecoration']);
Route::post('/Settings::setOverlayType', [Administration\SettingsController::class, 'setImageOverlayType']);
Route::post('/Settings::setNSFWVisible', [Administration\SettingsController::class, 'setNSFWVisible']);
Route::post('/Settings::setDropboxKey', [Administration\SettingsController::class, 'setDropboxKey']);
Route::post('/Settings::setNewPhotosNotification', [Administration\SettingsController::class, 'setNewPhotosNotification']);
Route::post('/Settings::setSmartAlbumVisibility', [Administration\SettingsController::class, 'setSmartAlbumVisibility']);

/**
 * UPDATE.
 */
Route::post('/Update::apply', [Administration\UpdateController::class, 'apply']);
Route::post('/Update::check', [Administration\UpdateController::class, 'check']);

