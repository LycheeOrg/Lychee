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

Route::get('/LandingPage', [LandingPageController::class, '__invoke']);
// Route::get('/Config::init', [ConfigController::class, 'init']);
Route::get('/Gallery::Init', [Gallery\ConfigController::class, 'getInit']);
Route::get('/Gallery::getLayout', [Gallery\ConfigController::class, 'getGalleryLayout']);
Route::get('/Gallery::getMapProvider', [Gallery\ConfigController::class, 'getMapProvider']);
Route::get('/Gallery::getUploadLimits', [Gallery\ConfigController::class, 'getUploadCOnfig']);

/**
 * ALBUMS.
 */
Route::get('/Albums', [Gallery\AlbumsController::class, 'get'])->middleware(['login_required:root']);

/**
 * ALBUM.
 */
Route::get('/Album', [Gallery\AlbumController::class, 'get'])->middleware(['login_required:album']);
Route::get('/Album::getTargetListAlbums', [Gallery\AlbumController::class, 'getTargetListAlbums'])->middleware(['login_required:album']);
Route::post('/Album', [Gallery\AlbumController::class, 'createAlbum']);
Route::patch('/Album', [Gallery\AlbumController::class, 'updateAlbum']);
Route::post('/TagAlbum', [Gallery\AlbumController::class, 'createTagAlbum']);
Route::patch('/TagAlbum', [Gallery\AlbumController::class, 'updateTagAlbum']);
Route::post('/Album::updateProtectionPolicy', [Gallery\AlbumController::class, 'updateProtectionPolicy']);
Route::post('/Album::delete', [Gallery\AlbumController::class, 'delete']);
Route::post('/Album::transfer', [Gallery\AlbumController::class, 'transfer']);
Route::post('/Album::move', [Gallery\AlbumController::class, 'move']);
// Route::post('/Album::merge', [AlbumController::class, 'merge']);

// Route::post('/Albums::getPositionData', [AlbumsController::class, 'getPositionData'])->middleware(['login_required:root']);
// Route::post('/Albums::tree', [AlbumsController::class, 'tree'])->middleware(['login_required:root']);

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
 * Sharing.
 */
Route::get('/Sharing', [Gallery\SharingController::class, 'list']);
Route::post('/Sharing', [Gallery\SharingController::class, 'create']);
Route::post('/Sharing::edit', [Gallery\SharingController::class, 'edit']);
Route::post('/Sharing::delete', [Gallery\SharingController::class, 'delete']);

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
Route::post('/Photo::fromUrl', [Gallery\PhotoController::class, 'fromUrl']);
Route::post('/Photo', [Gallery\PhotoController::class, 'upload'])
	->withoutMiddleware(['content_type:json'])
	->middleware(['content_type:multipart']);
Route::patch('/Photo', [Gallery\PhotoController::class, 'update']);
Route::post('/Photo::move', [Gallery\PhotoController::class, 'move']);
Route::delete('/Photo', [Gallery\PhotoController::class, 'delete']);

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
Route::post('/Profile::update', [ProfileController::class, 'update']);
Route::post('/Profile::resetToken', [ProfileController::class, 'resetToken']);
Route::post('/Profile::unsetToken', [ProfileController::class, 'unsetToken']);

/**
 * USERS.
 */
Route::get('/Users', [UsersController::class, 'list']);
Route::get('/Users::count', [UsersController::class, 'count']);

/**
 * USERS MANAGEMENT.
 */
Route::get('/UserManagement', [Admin\UserManagementController::class, 'list']);
Route::post('/UserManagement::save', [Admin\UserManagementController::class, 'save']);
Route::post('/UserManagement::delete', [Admin\UserManagementController::class, 'delete']);
Route::post('/UserManagement::create', [Admin\UserManagementController::class, 'create']);

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
Route::get('/Diagnostics', [Admin\DiagnosticsController::class, 'errors']);
Route::get('/Diagnostics::info', [Admin\DiagnosticsController::class, 'info']);
Route::get('/Diagnostics::space', [Admin\DiagnosticsController::class, 'space']);
Route::get('/Diagnostics::config', [Admin\DiagnosticsController::class, 'config']);
Route::get('/Diagnostics::permissions', [Admin\DiagnosticsController::class, 'getFullAccessPermissions']);

/**
 * JOBS.
 */
Route::get('/Jobs', [Admin\JobsController::class, 'list']);

// Route::post('/Diagnostics::getSize', [AdministrationDiagnosticsController::class, 'getSize']);

/**
 * SETTINGS.
 */
Route::get('/Settings', [Admin\SettingsController::class, 'getAll']);
Route::post('/Settings::setConfigs', [Admin\SettingsController::class, 'setConfigs']);
Route::get('/Settings::getLanguages', [Admin\SettingsController::class, 'getLanguages']);

/**
 * MAINTENANCE.
 */
Route::get('/Maintenance::update', [Admin\UpdateController::class, 'get']);
Route::post('/Maintenance::update', [Admin\UpdateController::class, 'check']);
Route::get('/Maintenance::cleaning', [Admin\Maintenance\Cleaning::class, 'check']);
Route::post('/Maintenance::cleaning', [Admin\Maintenance\Cleaning::class, 'do']);
Route::get('/Maintenance::jobs', [Admin\Maintenance\FixJobs::class, 'check']);
Route::post('/Maintenance::jobs', [Admin\Maintenance\FixJobs::class, 'do']);
Route::get('/Maintenance::tree', [Admin\Maintenance\FixTree::class, 'check']);
Route::post('/Maintenance::tree', [Admin\Maintenance\FixTree::class, 'do']);
Route::get('/Maintenance::genSizeVariants', [Admin\Maintenance\GenSizeVariants::class, 'check']);
Route::post('/Maintenance::genSizeVariants', [Admin\Maintenance\GenSizeVariants::class, 'do']);
Route::get('/Maintenance::missingFileSize', [Admin\Maintenance\MissingFileSizes::class, 'check']);
Route::post('/Maintenance::missingFileSize', [Admin\Maintenance\MissingFileSizes::class, 'do']);
Route::post('/Maintenance::optimize', [Admin\Maintenance\Optimize::class, 'do']);

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
