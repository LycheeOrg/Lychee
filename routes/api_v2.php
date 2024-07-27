<?php

namespace App\Http\Controllers;

use App\Exceptions\Internal\NotImplementedException;
use App\Http\Controllers\Gallery\AlbumsController;
use App\Legacy\V1\Controllers\Administration\DiagnosticsController as AdministrationDiagnosticsController;
use App\Legacy\V1\Controllers\Administration\SettingsController as AdministrationSettingsController;
use App\Legacy\V1\Controllers\Administration\SharingController as AdministrationSharingController;
use App\Legacy\V1\Controllers\Administration\UpdateController as AdministrationUpdateController;
use App\Legacy\V1\Controllers\Administration\UsersController as AdministrationUsersController;
use App\Legacy\V1\Controllers\AlbumController;
// use App\Legacy\V1\Controllers\AlbumsController;
use App\Legacy\V1\Controllers\ImportController;
use App\Legacy\V1\Controllers\LegacyController;
use App\Legacy\V1\Controllers\PhotoController;
use App\Legacy\V1\Controllers\PhotoEditorController;
use App\Legacy\V1\Controllers\SearchController;
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

Route::get('/LandingPage', [LandingPageController::class, '__invoke']);
Route::get('/Config::init', [ConfigController::class, 'init']);
Route::get('/Gallery::getLayout', [Gallery\ConfigController::class, 'getGalleryLayout']);
Route::get('/Gallery::getMapProvider', [Gallery\ConfigController::class, 'getMapProvider']);

/**
 * ALBUMS.
 */
Route::get('/Albums', [Gallery\AlbumsController::class, 'get'])->middleware(['login_required:root']);
Route::get('/Album', [Gallery\AlbumController::class, 'get'])->middleware(['login_required:album']);
Route::post('/Album::update', [Gallery\AlbumController::class, 'updateAlbum']);
Route::post('/Album::updateTag', [Gallery\AlbumController::class, 'updateTagAlbum']);
Route::post('/Album::updateProtectionPolicy', [Gallery\AlbumController::class, 'updateProtectionPolicy']);

// Route::post('/Albums::getPositionData', [AlbumsController::class, 'getPositionData'])->middleware(['login_required:root']);
// Route::post('/Albums::tree', [AlbumsController::class, 'tree'])->middleware(['login_required:root']);

/**
 * ALBUM.
 */
// Route::post('/Album::get', [AlbumController::class, 'get'])->middleware(['login_required:album']);
// Route::post('/Album::getPositionData', [AlbumController::class, 'getPositionData']);
// Route::post('/Album::unlock', [AlbumController::class, 'unlock']);
// Route::post('/Album::add', [AlbumController::class, 'add']);
// Route::post('/Album::addByTags', [AlbumController::class, 'addTagAlbum']);
// Route::post('/Album::setTitle', [AlbumController::class, 'setTitle']);
// Route::post('/Album::setNSFW', [AlbumController::class, 'setNSFW']);
// Route::post('/Album::setDescription', [AlbumController::class, 'setDescription']);
// Route::post('/Album::setCopyright', [AlbumController::class, 'setCopyright']);
// Route::post('/Album::setCover', [AlbumController::class, 'setCover']);
// Route::post('/Album::setHeader', [AlbumController::class, 'setHeader']);
// Route::post('/Album::setShowTags', [AlbumController::class, 'setShowTags']);
// Route::post('/Album::setProtectionPolicy', [AlbumController::class, 'setProtectionPolicy']);
// Route::post('/Album::delete', [AlbumController::class, 'delete']);
// Route::post('/Album::merge', [AlbumController::class, 'merge']);
// Route::post('/Album::move', [AlbumController::class, 'move']);
// Route::post('/Album::setLicense', [AlbumController::class, 'setLicense']);
// Route::post('/Album::setSorting', [AlbumController::class, 'setSorting']);
// Route::get('/Album::getArchive', [AlbumController::class, 'getArchive'])
// 	->name('download')
// 	->withoutMiddleware(['content_type:json', 'accept_content_type:json'])
// 	->middleware(['accept_content_type:any']);
// Route::post('/Album::setTrack', [AlbumController::class, 'setTrack'])
// 	->withoutMiddleware(['content_type:json'])
// 	->middleware(['content_type:multipart']);
// Route::post('/Album::deleteTrack', [AlbumController::class, 'deleteTrack']);

/**
 * IMPORT.
 */
// Route::post('/Import::url', [ImportController::class, 'url']);
// Route::post('/Import::server', [ImportController::class, 'server']);
// Route::post('/Import::serverCancel', [ImportController::class, 'serverCancel']);

/**
 * LEGACY.
 */
// Route::post('/Legacy::translateLegacyModelIDs', [LegacyController::class, 'translateLegacyModelIDs']);

/**
 * PHOTO.
 */
Route::get('/Photo', [Gallery\PhotoController::class, 'get'])->middleware(['login_required:album']);
// Route::post('/Photo::getRandom', [PhotoController::class, 'getRandom']);
// Route::post('/Photo::setTitle', [PhotoController::class, 'setTitle']);
// Route::post('/Photo::setDescription', [PhotoController::class, 'setDescription']);
// Route::post('/Photo::setStar', [PhotoController::class, 'setStar']);
// Route::post('/Photo::setPublic', fn () => throw new NotImplementedException('This code is deprecated. Good bye.')); // just legacy stuff.
// Route::post('/Photo::setAlbum', [PhotoController::class, 'setAlbum']);
// Route::post('/Photo::setTags', [PhotoController::class, 'setTags']);
// Route::post('/Photo::delete', [PhotoController::class, 'delete']);
// Route::post('/Photo::duplicate', [PhotoController::class, 'duplicate']);
// Route::post('/Photo::setLicense', [PhotoController::class, 'setLicense']);
// Route::post('/Photo::setUploadDate', [PhotoController::class, 'setUploadDate']);
// Route::post('/Photo::clearSymLink', [PhotoController::class, 'clearSymLink']);
// Route::post('/PhotoEditor::rotate', [PhotoEditorController::class, 'rotate']);
// Route::post('/Photo::add', [PhotoController::class, 'add'])
// 	->withoutMiddleware(['content_type:json'])
// 	->middleware(['content_type:multipart']);
// Route::get('/Photo::getArchive', [PhotoController::class, 'getArchive'])
// 	->name('photo_download')
// 	->withoutMiddleware(['content_type:json', 'accept_content_type:json'])
// 	->middleware(['accept_content_type:any']);

/**
 * SEARCH.
 */
// Route::post('/Search::run', [SearchController::class, 'run']);

/**
 * SESSION.
 */
Route::post('/Auth::login', [AuthController::class, 'login']);
Route::post('/Auth::logout', [AuthController::class, 'logout']);
Route::get('/Auth::user', [AuthController::class, 'getCurrentUser']);
Route::get('/Auth::rights', [AuthController::class, 'getGlobalRights']);
Route::get('/Auth::config', [AuthController::class, 'getConfig']);

/**
 * USER.
 */
Route::post('/Profile::updateLogin', [ProfileController::class, 'updateLogin']);
Route::post('/Profile::setEmail', [ProfileController::class, 'setEmail']);
Route::post('/Profile::resetToken', [ProfileController::class, 'resetToken']);
Route::post('/Profile::unsetToken', [ProfileController::class, 'unsetToken']);

/**
 * USERS.
 */
Route::get('/Users::count', [Admin\UsersController::class, 'count']);
// Route::post('/Users::list', [AdministrationUsersController::class, 'list']);
// Route::post('/Users::save', [AdministrationUsersController::class, 'save']);
// Route::post('/Users::delete', [AdministrationUsersController::class, 'delete']);
// Route::post('/Users::create', [AdministrationUsersController::class, 'create']);

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
// Route::post('/Sharing::list', [AdministrationSharingController::class, 'list']);
// Route::post('/Sharing::add', [AdministrationSharingController::class, 'add']);
// Route::post('/Sharing::setByAlbum', [AdministrationSharingController::class, 'setByAlbum']);
// Route::post('/Sharing::delete', [AdministrationSharingController::class, 'delete']);

/**
 * DIAGNOSTICS.
 */
// Route::post('/Diagnostics::get', [AdministrationDiagnosticsController::class, 'get']);
// Route::post('/Diagnostics::getSize', [AdministrationDiagnosticsController::class, 'getSize']);

/**
 * SETTINGS.
 */
Route::get('/Settings', [Admin\SettingsController::class, 'getAll']);
Route::post('/Settings::setConfigs', [Admin\SettingsController::class, 'setConfigs']);

// Route::post('/Settings::setSorting', [AdministrationSettingsController::class, 'setSorting']);
// Route::post('/Settings::setLang', [AdministrationSettingsController::class, 'setLang']);
// Route::post('/Settings::setLayout', [AdministrationSettingsController::class, 'setLayout']);
// Route::post('/Settings::setPublicSearch', [AdministrationSettingsController::class, 'setPublicSearch']);
// Route::post('/Settings::setDefaultLicense', [AdministrationSettingsController::class, 'setDefaultLicense']);
// Route::post('/Settings::setMapDisplay', [AdministrationSettingsController::class, 'setMapDisplay']);
// Route::post('/Settings::setMapDisplayPublic', [AdministrationSettingsController::class, 'setMapDisplayPublic']);
// Route::post('/Settings::setMapProvider', [AdministrationSettingsController::class, 'setMapProvider']);
// Route::post('/Settings::setMapIncludeSubAlbums', [AdministrationSettingsController::class, 'setMapIncludeSubAlbums']);
// Route::post('/Settings::setLocationDecoding', [AdministrationSettingsController::class, 'setLocationDecoding']);
// Route::post('/Settings::setLocationShow', [AdministrationSettingsController::class, 'setLocationShow']);
// Route::post('/Settings::setLocationShowPublic', [AdministrationSettingsController::class, 'setLocationShowPublic']);
// Route::post('/Settings::setCSS', [AdministrationSettingsController::class, 'setCSS']);
// Route::post('/Settings::setJS', [AdministrationSettingsController::class, 'setJS']);
// Route::post('/Settings::getAll', [AdministrationSettingsController::class, 'getAll']);
// Route::post('/Settings::saveAll', [AdministrationSettingsController::class, 'saveAll']);
// Route::post('/Settings::setAlbumDecoration', [AdministrationSettingsController::class, 'setAlbumDecoration']);
// Route::post('/Settings::setOverlayType', [AdministrationSettingsController::class, 'setImageOverlayType']);
// Route::post('/Settings::setNSFWVisible', [AdministrationSettingsController::class, 'setNSFWVisible']);
// Route::post('/Settings::setDropboxKey', [AdministrationSettingsController::class, 'setDropboxKey']);
// Route::post('/Settings::setNewPhotosNotification', [AdministrationSettingsController::class, 'setNewPhotosNotification']);
// Route::post('/Settings::setSmartAlbumVisibility', [AdministrationSettingsController::class, 'setSmartAlbumVisibility']);

/**
 * UPDATE.
 */
// Route::post('/Update::apply', [AdministrationUpdateController::class, 'apply']);
// Route::post('/Update::check', [AdministrationUpdateController::class, 'check']);
Route::get('/Version', [VersionController::class, 'get']);
