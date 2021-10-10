<?php

namespace App\Http\Controllers\Administration;

use App\Http\Middleware\AdminCheck;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "admin" middleware group. Now create something great!
|
*/

// We need that to force https everywhere
//if (env('APP_ENV') === 'production') {

if (env('APP_ENV') === 'dev') {
	URL::forceScheme('https');
}

Route::get('/Logs', [LogController::class, 'display']);
Route::post('/api/Logs', [LogController::class, 'display']);
Route::get('/api/Logs::clear', [LogController::class, 'clear']);
Route::post('/api/Logs::clear', [LogController::class, 'clear']);
Route::post('/api/Logs::clearNoise', [LogController::class, 'clearNoise']);

Route::post('/api/Settings::setSorting', [SettingsController::class, 'setSorting']);
Route::post('/api/Settings::setLang', [SettingsController::class, 'setLang']);
Route::post('/api/Settings::setLayout', [SettingsController::class, 'setLayout']);
Route::post('/api/Settings::setPublicSearch', [SettingsController::class, 'setPublicSearch']);
Route::post('/api/Settings::setDefaultLicense', [SettingsController::class, 'setDefaultLicense']);
Route::post('/api/Settings::setMapDisplay', [SettingsController::class, 'setMapDisplay']);
Route::post('/api/Settings::setMapDisplayPublic', [SettingsController::class, 'setMapDisplayPublic']);
Route::post('/api/Settings::setMapProvider', [SettingsController::class, 'setMapProvider']);
Route::post('/api/Settings::setMapIncludeSubAlbums', [SettingsController::class, 'setMapIncludeSubAlbums']);
Route::post('/api/Settings::setLocationDecoding', [SettingsController::class, 'setLocationDecoding']);
Route::post('/api/Settings::setLocationShow', [SettingsController::class, 'setLocationShow']);
Route::post('/api/Settings::setLocationShowPublic', [SettingsController::class, 'setLocationShowPublic']);
Route::post('/api/Settings::setCSS', [SettingsController::class, 'setCSS']);
Route::post('/api/Settings::getAll', [SettingsController::class, 'getAll']);
Route::post('/api/Settings::saveAll', [SettingsController::class, 'saveAll']);
Route::post('/api/Settings::setOverlayType', [SettingsController::class, 'setImageOverlayType']);
Route::post('/api/Settings::setNSFWVisible', [SettingsController::class, 'setNSFWVisible']);
Route::post('/api/Settings::setDropboxKey', [SettingsController::class, 'setDropboxKey']);
Route::post('/api/Settings::setNewPhotosNotification', [SettingsController::class, 'setNewPhotosNotification']);

Route::get('/Update', [UpdateController::class, 'apply']);
Route::post('/api/Update::Apply', [UpdateController::class, 'apply']);
Route::post('/api/Update::Check', [UpdateController::class, 'check']);

// For the first line below, refer to the comment in `UserController::list`.
Route::post('/api/User::List', [UserController::class, 'list'])->withoutMiddleware([AdminCheck::class]);
Route::post('/api/User::Save', [UserController::class, 'save']);
Route::post('/api/User::Delete', [UserController::class, 'delete']);
Route::post('/api/User::Create', [UserController::class, 'create']);
Route::post('/api/User::UpdateEmail', [UserController::class, 'updateEmail'])->withoutMiddleware([AdminCheck::class])->middleware('login');
Route::post('/api/User::GetEmail', [UserController::class, 'getEmail'])->withoutMiddleware([AdminCheck::class])->middleware('login');
