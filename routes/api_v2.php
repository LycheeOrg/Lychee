<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers;

use App\Http\Controllers\Gallery\SearchController;
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
Route::get('/Frame', [Gallery\FrameController::class, 'get']);

Route::get('/Gallery::Init', [Gallery\ConfigController::class, 'getInit']);
Route::get('/Gallery::Footer', [Gallery\ConfigController::class, 'getFooter'])->middleware(['cache_control']);
Route::get('/Gallery::getLayout', [Gallery\ConfigController::class, 'getGalleryLayout'])->middleware(['cache_control']);
Route::get('/Gallery::getUploadLimits', [Gallery\ConfigController::class, 'getUploadCOnfig'])->middleware(['cache_control']);

/**
 * ALBUMS.
 */
Route::get('/Albums', [Gallery\AlbumsController::class, 'get'])->middleware(['login_required:root', 'cache_control']);

/**
 * ALBUM.
 */
Route::get('/Album', [Gallery\AlbumController::class, 'get'])->middleware(['login_required:album', 'cache_control']);
Route::get('/Album::getTargetListAlbums', [Gallery\AlbumController::class, 'getTargetListAlbums'])->middleware(['login_required:album', 'cache_control']);
Route::post('/Album::unlock', [Gallery\AlbumController::class, 'unlock']);
Route::post('/Album', [Gallery\AlbumController::class, 'createAlbum']);
Route::patch('/Album', [Gallery\AlbumController::class, 'updateAlbum']);
Route::patch('/Album::rename', [Gallery\AlbumController::class, 'rename']);
Route::post('/Album::updateProtectionPolicy', [Gallery\AlbumController::class, 'updateProtectionPolicy']);
Route::delete('/Album', [Gallery\AlbumController::class, 'delete']);
Route::post('/Album::move', [Gallery\AlbumController::class, 'move']);
Route::post('/Album::cover', [Gallery\AlbumController::class, 'cover']);
Route::post('/Album::header', [Gallery\AlbumController::class, 'header']);
Route::post('/Album::merge', [Gallery\AlbumController::class, 'merge']);
Route::post('/Album::transfer', [Gallery\AlbumController::class, 'transfer']);
Route::post('/Album::track', [Gallery\AlbumController::class, 'setTrack'])
	->withoutMiddleware(['content_type:json'])
	->middleware(['content_type:multipart']);
Route::delete('/Album::track', [Gallery\AlbumController::class, 'deleteTrack']);

Route::post('/TagAlbum', [Gallery\AlbumController::class, 'createTagAlbum']);
Route::patch('/TagAlbum', [Gallery\AlbumController::class, 'updateTagAlbum']);
Route::get('/Zip', [Gallery\AlbumController::class, 'getArchive'])
	->name('download')
	->withoutMiddleware(['content_type:json', 'accept_content_type:json'])
	->middleware(['accept_content_type:any']);

/**
 * MAP.
 */
Route::get('/Map', [Gallery\MapController::class, 'getData'])->middleware(['cache_control']);
Route::get('/Map::provider', [Gallery\MapController::class, 'getProvider'])->middleware(['cache_control']);

/**
 * Sharing.
 */
Route::get('/Sharing', [Gallery\SharingController::class, 'list']);
Route::post('/Sharing', [Gallery\SharingController::class, 'create']);
Route::put('/Sharing', [Gallery\SharingController::class, 'propagate']);
Route::patch('/Sharing', [Gallery\SharingController::class, 'edit']);
Route::delete('/Sharing', [Gallery\SharingController::class, 'delete']);
Route::get('/Sharing::all', [Gallery\SharingController::class, 'listAll']);

/**
 * IMPORT.
 */
// Route::post('/Import::server', [ImportController::class, 'server']);
// Route::post('/Import::serverCancel', [ImportController::class, 'serverCancel']);

/**
 * LEGACY.
 */
// Route::post('/Legacy::translateLegacyModelIDs', [LegacyController::class, 'translateLegacyModelIDs']);

/**
 * PHOTO.
 */
Route::get('/Photo::random', [Gallery\FrameController::class, 'random']);
Route::post('/Photo::fromUrl', [Gallery\PhotoController::class, 'fromUrl']);
Route::post('/Photo', [Gallery\PhotoController::class, 'upload'])
	->withoutMiddleware(['content_type:json'])
	->middleware(['content_type:multipart']);
Route::patch('/Photo', [Gallery\PhotoController::class, 'update']);
Route::patch('/Photo::rename', [Gallery\PhotoController::class, 'rename']);
Route::patch('/Photo::tags', [Gallery\PhotoController::class, 'tags']);
Route::post('/Photo::move', [Gallery\PhotoController::class, 'move']);
Route::post('/Photo::copy', [Gallery\PhotoController::class, 'copy']);
Route::post('/Photo::star', [Gallery\PhotoController::class, 'star']);
Route::post('/Photo::rotate', [Gallery\PhotoController::class, 'rotate']);
Route::delete('/Photo', [Gallery\PhotoController::class, 'delete']);

// Route::post('/Photo::clearSymLink', [PhotoController::class, 'clearSymLink']);
// Route::get('/Photo::getArchive', [PhotoController::class, 'getArchive'])
// 	->name('photo_download')
// 	->withoutMiddleware(['content_type:json', 'accept_content_type:json'])
// 	->middleware(['accept_content_type:any']);

/**
 * SEARCH.
 */
Route::get('/Search::init', [SearchController::class, 'init'])->middleware(['cache_control']);
Route::get('/Search', [SearchController::class, 'search'])->middleware(['cache_control']);

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
Route::get('/Users::count', [UsersController::class, 'count'])->middleware(['cache_control']);

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
Route::get('/WebAuthn', [WebAuthn\WebAuthnManageController::class, 'list']);
Route::patch('/WebAuthn', [WebAuthn\WebAuthnManageController::class, 'edit']);
Route::delete('/WebAuthn', [WebAuthn\WebAuthnManageController::class, 'delete']);

// Special Webauthn operations
Route::post('/WebAuthn::register/options', [WebAuthn\WebAuthnRegisterController::class, 'options'])
	->name('webauthn.register.options');
Route::post('/WebAuthn::register', [WebAuthn\WebAuthnRegisterController::class, 'register'])
	->name('webauthn.register');
Route::post('/WebAuthn::login/options', [WebAuthn\WebAuthnLoginController::class, 'options'])
	->name('webauthn.login.options');
Route::post('/WebAuthn::login', [WebAuthn\WebAuthnLoginController::class, 'login'])
	->name('webauthn.login');

/**
 * OAUTH.
 */
// This route returns different results depending whether we are authenticated or not:
// If Authenticated: list of the registrated Oauth providers
// If not Authenticated: list of the available Oauth providers
Route::get('/Oauth', [OauthController::class, 'list']);
Route::delete('/Oauth', [OauthController::class, 'clear']);

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

/**
 * SETTINGS.
 */
Route::get('/Settings', [Admin\SettingsController::class, 'getAll']);
Route::post('/Settings::setConfigs', [Admin\SettingsController::class, 'setConfigs'])->middleware(['config_integrity']);
Route::get('/Settings::getLanguages', [Admin\SettingsController::class, 'getLanguages']);
Route::post('/Settings::setCSS', [Admin\SettingsController::class, 'setCSS']);
Route::post('/Settings::setJS', [Admin\SettingsController::class, 'setJS']);

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
Route::get('/Maintenance::genSizeVariants', [Admin\Maintenance\GenSizeVariants::class, 'check']);
Route::post('/Maintenance::genSizeVariants', [Admin\Maintenance\GenSizeVariants::class, 'do']);
Route::get('/Maintenance::missingFileSize', [Admin\Maintenance\MissingFileSizes::class, 'check']);
Route::post('/Maintenance::missingFileSize', [Admin\Maintenance\MissingFileSizes::class, 'do']);
Route::post('/Maintenance::optimize', [Admin\Maintenance\Optimize::class, 'do']);
Route::post('/Maintenance::flushCache', [Admin\Maintenance\FlushCache::class, 'do']);
Route::post('/Maintenance::register', Admin\Maintenance\RegisterController::class);
Route::get('/Maintenance::fullTree', [Admin\Maintenance\FullTree::class, 'check']);
Route::post('/Maintenance::fullTree', [Admin\Maintenance\FullTree::class, 'do']);
Route::get('/Maintenance::countDuplicates', [Admin\Maintenance\DuplicateFinder::class, 'check']);
Route::get('/Maintenance::searchDuplicates', [Admin\Maintenance\DuplicateFinder::class, 'get']);

/**
 * STATISTICS.
 */
Route::get('/Statistics::userSpace', [StatisticsController::class, 'getSpacePerUser'])->middleware(['support:se']);
Route::get('/Statistics::sizeVariantSpace', [StatisticsController::class, 'getSpacePerSizeVariantType'])->middleware(['support:se']);
Route::get('/Statistics::albumSpace', [StatisticsController::class, 'getSpacePerAlbum'])->middleware(['support:se']);
Route::get('/Statistics::totalAlbumSpace', [StatisticsController::class, 'getTotalSpacePerAlbum'])->middleware(['support:se']);

/**
 * UPDATE.
 */
// Route::post('/Update::apply', [AdministrationUpdateController::class, 'apply']);
// Route::post('/Update::check', [AdministrationUpdateController::class, 'check']);
Route::get('/Version', [VersionController::class, 'get']);
