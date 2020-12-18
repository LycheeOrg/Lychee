<?php

namespace App\Http\Controllers\Administration;

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

Route::post('/api/Settings::setSorting', [SettingsController::class, 'setSorting']);
Route::post('/api/Settings::setLang', [SettingsController::class, 'setLang']);
Route::post('/api/Settings::setLayout', [SettingsController::class, 'setLayout']);
Route::post('/api/Settings::setPublicSearch', [SettingsController::class, 'setPublicSearch']);
Route::post('/api/Settings::setImageOverlay', [SettingsController::class, 'setImageOverlay']);
Route::post('/api/Settings::setDefaultLicense', [SettingsController::class, 'setDefaultLicense']);
Route::post('/api/Settings::setMapDisplay', [SettingsController::class, 'setMapDisplay']);
Route::post('/api/Settings::setMapDisplayPublic', [SettingsController::class, 'setMapDisplayPublic']);
Route::post('/api/Settings::setMapProvider', [SettingsController::class, 'setMapProvider']);
Route::post('/api/Settings::setMapIncludeSubalbums', [SettingsController::class, 'setMapIncludeSubalbums']);
Route::post('/api/Settings::setLocationDecoding', [SettingsController::class, 'setLocationDecoding']);
Route::post('/api/Settings::setLocationShow', [SettingsController::class, 'setLocationShow']);
Route::post('/api/Settings::setLocationShowPublic', [SettingsController::class, 'setLocationShowPublic']);
Route::post('/api/Settings::setCSS', [SettingsController::class, 'setCSS']);
Route::post('/api/Settings::getAll', [SettingsController::class, 'getAll']);
Route::post('/api/Settings::saveAll', [SettingsController::class, 'saveAll']);
Route::post('/api/Settings::setOverlayType', [SettingsController::class, 'setImageOverlayType']);
Route::post('/api/Settings::setDropboxKey', [SettingsController::class, 'setDropboxKey']);
