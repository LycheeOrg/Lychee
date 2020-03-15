<?php

/*
|--------------------------------------------------------------------------
| Install Routes
|--------------------------------------------------------------------------
|
| Here is where you can register install routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "install" middleware group. Now create something great!
|
*/

// We need that to force https everywhere
//if (env('APP_ENV') === 'production') {

if (env('APP_ENV') === 'dev') {
	URL::forceScheme('https');
}

Route::post('/api/Settings::setSorting', 'SettingsController@setSorting');
Route::post('/api/Settings::setLang', 'SettingsController@setLang');
Route::post('/api/Settings::setLayout', 'SettingsController@setLayout');
Route::post('/api/Settings::setPublicSearch', 'SettingsController@setPublicSearch');
Route::post('/api/Settings::setImageOverlay', 'SettingsController@setImageOverlay');
Route::post('/api/Settings::setDefaultLicense', 'SettingsController@setDefaultLicense');
Route::post('/api/Settings::setMapDisplay', 'SettingsController@setMapDisplay');
Route::post('/api/Settings::setMapDisplayPublic', 'SettingsController@setMapDisplayPublic');
Route::post('/api/Settings::setMapProvider', 'SettingsController@setMapProvider');
Route::post('/api/Settings::setMapIncludeSubalbums', 'SettingsController@setMapIncludeSubalbums');
Route::post('/api/Settings::setCSS', 'SettingsController@setCSS');
Route::post('/api/Settings::getAll', 'SettingsController@getAll');
Route::post('/api/Settings::saveAll', 'SettingsController@saveAll');
Route::post('/api/Settings::setOverlayType', 'SettingsController@setImageOverlayType');
Route::post('/api/Settings::setDropboxKey', 'SettingsController@setDropboxKey');
