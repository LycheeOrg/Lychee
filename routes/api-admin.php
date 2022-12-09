<?php

namespace App\Http\Controllers\Administration;

use App\Http\Middleware\AdminCheck;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

// We need that to force https everywhere
// if (env('APP_ENV') === 'production') {

if (env('APP_ENV') === 'dev') {
	URL::forceScheme('https');
}

Route::post('/Diagnostics::get', [DiagnosticsController::class, 'get']);
Route::post('/Diagnostics::getSize', [DiagnosticsController::class, 'getSize']);

Route::post('/Logs::list', [LogController::class, 'list']);
Route::post('/Logs::clear', [LogController::class, 'clear']);
Route::post('/Logs::clearNoise', [LogController::class, 'clearNoise']);

Route::post('/Settings::setSorting', [SettingsController::class, 'setSorting']);
Route::post('/Settings::setLang', [SettingsController::class, 'setLang']);
Route::post('/Settings::setLayout', [SettingsController::class, 'setLayout']);
Route::post('/Settings::setPublicSearch', [SettingsController::class, 'setPublicSearch']);
Route::post('/Settings::setDefaultLicense', [SettingsController::class, 'setDefaultLicense']);
Route::post('/Settings::setMapDisplay', [SettingsController::class, 'setMapDisplay']);
Route::post('/Settings::setMapDisplayPublic', [SettingsController::class, 'setMapDisplayPublic']);
Route::post('/Settings::setMapProvider', [SettingsController::class, 'setMapProvider']);
Route::post('/Settings::setMapIncludeSubAlbums', [SettingsController::class, 'setMapIncludeSubAlbums']);
Route::post('/Settings::setLocationDecoding', [SettingsController::class, 'setLocationDecoding']);
Route::post('/Settings::setLocationShow', [SettingsController::class, 'setLocationShow']);
Route::post('/Settings::setLocationShowPublic', [SettingsController::class, 'setLocationShowPublic']);
Route::post('/Settings::setCSS', [SettingsController::class, 'setCSS']);
Route::post('/Settings::getAll', [SettingsController::class, 'getAll']);
Route::post('/Settings::saveAll', [SettingsController::class, 'saveAll']);
Route::post('/Settings::setOverlayType', [SettingsController::class, 'setImageOverlayType']);
Route::post('/Settings::setNSFWVisible', [SettingsController::class, 'setNSFWVisible']);
Route::post('/Settings::setDropboxKey', [SettingsController::class, 'setDropboxKey']);
Route::post('/Settings::setNewPhotosNotification', [SettingsController::class, 'setNewPhotosNotification']);
Route::post('/Settings::setSubalbumCount', [SettingsController::class, 'setSubalbumCount']);

Route::post('/Update::apply', [UpdateController::class, 'apply']);
Route::post('/Update::check', [UpdateController::class, 'check']);

Route::post('/User::list', [UserController::class, 'list']);
Route::post('/User::save', [UserController::class, 'save']);
Route::post('/User::delete', [UserController::class, 'delete']);
Route::post('/User::create', [UserController::class, 'create']);
Route::post('/User::setEmail', [UserController::class, 'setEmail'])->withoutMiddleware([AdminCheck::class]);
Route::post('/User::getEmail', [UserController::class, 'getEmail'])->withoutMiddleware([AdminCheck::class]);
Route::post('/User::getAuthenticatedUser', [UserController::class, 'getAuthenticatedUser'])->withoutMiddleware([AdminCheck::class]);
Route::post('/User::resetToken', [UserController::class, 'resetToken'])->withoutMiddleware([AdminCheck::class]);
Route::post('/User::unsetToken', [UserController::class, 'unsetToken'])->withoutMiddleware([AdminCheck::class]);
