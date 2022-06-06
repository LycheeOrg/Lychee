<?php

namespace App\Http\Controllers\Administration;

use App\Http\Middleware\AdminCheck;
use Illuminate\Support\Facades\Route;

Route::get('/diagnostics', [DiagnosticsController::class, 'get']);
Route::get('/diagnostics/size', [DiagnosticsController::class, 'getSize']);

Route::get('/logs', [LogController::class, 'list']);
Route::post('/logs/clear', [LogController::class, 'clear']);
Route::post('/logs/clearNoise', [LogController::class, 'clearNoise']);

Route::prefix('settings')->group(function () {
	Route::get('', [SettingsController::class, 'getAll']);
	Route::post('', [SettingsController::class, 'saveAll']);
	Route::post('/sorting', [SettingsController::class, 'setSorting']);
	Route::post('/lang', [SettingsController::class, 'setLang']);
	Route::post('/layout', [SettingsController::class, 'setLayout']);
	Route::post('/publicSearch', [SettingsController::class, 'setPublicSearch']);
	Route::post('/defaultLicense', [SettingsController::class, 'setDefaultLicense']);
	Route::post('/mapDisplay', [SettingsController::class, 'setMapDisplay']);
	Route::post('/mapDisplayPublic', [SettingsController::class, 'setMapDisplayPublic']);
	Route::post('/mapProvider', [SettingsController::class, 'setMapProvider']);
	Route::post('/mapIncludeSubAlbums', [SettingsController::class, 'setMapIncludeSubAlbums']);
	Route::post('/locationDecoding', [SettingsController::class, 'setLocationDecoding']);
	Route::post('/locationShow', [SettingsController::class, 'setLocationShow']);
	Route::post('/locationShowPublic', [SettingsController::class, 'setLocationShowPublic']);
	Route::post('/css', [SettingsController::class, 'setCSS']);
	Route::post('/overlayType', [SettingsController::class, 'setImageOverlayType']);
	Route::post('/nsfwVisible', [SettingsController::class, 'setNSFWVisible']);
	Route::post('/dropbox', [SettingsController::class, 'setDropboxKey']);
	Route::post('/newPhotosNotification', [SettingsController::class, 'setNewPhotosNotification']);
});

Route::get('/update', [UpdateController::class, 'check']);
Route::post('/update', [UpdateController::class, 'apply']);

Route::prefix('user')->group(function () {
	Route::get('', [UserController::class, 'list']);
	Route::post('', [UserController::class, 'create']);
	Route::post('/email', [UserController::class, 'setEmail'])->withoutMiddleware([AdminCheck::class]);
	Route::get('/email', [UserController::class, 'getEmail'])->withoutMiddleware([AdminCheck::class]);
	Route::post('/{userID}', [UserController::class, 'save']);
	Route::delete('/{userID}', [UserController::class, 'delete']);
});
