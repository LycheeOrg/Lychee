<?php

namespace App\Http\Controllers\Administration;

use App\Http\Middleware\AdminCheck;
use Illuminate\Support\Facades\Route;

Route::get('/diagnostics', [DiagnosticsController::class, 'get']);
Route::get('/diagnostics/size', [DiagnosticsController::class, 'getSize']);

Route::get('/logs', [LogController::class, 'list']);
Route::post('/logs/clear', [LogController::class, 'clear']);
Route::post('/logs/clearNoise', [LogController::class, 'clearNoise']);

Route::post('/settings/sorting', [SettingsController::class, 'setSorting']);
Route::post('/settings/lang', [SettingsController::class, 'setLang']);
Route::post('/settings/layout', [SettingsController::class, 'setLayout']);
Route::post('/settings/publicSearch', [SettingsController::class, 'setPublicSearch']);
Route::post('/settings/defaultLicense', [SettingsController::class, 'setDefaultLicense']);
Route::post('/settings/mapDisplay', [SettingsController::class, 'setMapDisplay']);
Route::post('/settings/mapDisplayPublic', [SettingsController::class, 'setMapDisplayPublic']);
Route::post('/settings/mapProvider', [SettingsController::class, 'setMapProvider']);
Route::post('/settings/mapIncludeSubAlbums', [SettingsController::class, 'setMapIncludeSubAlbums']);
Route::post('/settings/locationDecoding', [SettingsController::class, 'setLocationDecoding']);
Route::post('/settings/locationShow', [SettingsController::class, 'setLocationShow']);
Route::post('/settings/locationShowPublic', [SettingsController::class, 'setLocationShowPublic']);
Route::post('/settings/css', [SettingsController::class, 'setCSS']);
Route::get('/settings', [SettingsController::class, 'getAll']);
Route::post('/settings', [SettingsController::class, 'saveAll']);
Route::post('/settings/overlayType', [SettingsController::class, 'setImageOverlayType']);
Route::post('/settings/nsfwVisible', [SettingsController::class, 'setNSFWVisible']);
Route::post('/settings/dropbox', [SettingsController::class, 'setDropboxKey']);
Route::post('/settings/newPhotosNotification', [SettingsController::class, 'setNewPhotosNotification']);

Route::post('/update', [UpdateController::class, 'apply']);
Route::get('/update', [UpdateController::class, 'check']);

Route::get('/user', [UserController::class, 'list']);
Route::post('/user/{userID}', [UserController::class, 'save']);
Route::delete('/user/{userID}', [UserController::class, 'delete']);
Route::post('/user', [UserController::class, 'create']);
Route::post('/user/{userID}/email', [UserController::class, 'setEmail'])->withoutMiddleware([AdminCheck::class]);
Route::get('/user/{userID}/email', [UserController::class, 'getEmail'])->withoutMiddleware([AdminCheck::class]);
