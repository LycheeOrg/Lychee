<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// We need that to force https everywhere
//if (env('APP_ENV') === 'production') {

if (env('APP_ENV') === 'dev') {
	URL::forceScheme('https');
}

Route::get('/', 'IndexController@show')->name('home');
Route::get('/phpinfo', 'IndexController@phpinfo')->middleware('admin');
Route::get('/gallery', 'IndexController@gallery')->name('gallery');

Route::get('/view', 'ViewController@view');
Route::get('/demo', 'DemoController@js');
Route::get('/frame', 'FrameController@init')->name('frame');

Route::post('/php/index.php', 'SessionController@init'); // entry point if options are not initialized

Route::post('/api/Session::init', 'SessionController@init');
Route::post('/api/Session::login', 'SessionController@login');
Route::post('/api/Session::logout', 'SessionController@logout');

Route::post('/api/Albums::get', 'AlbumsController@get');
Route::post('/api/Albums::getPositionData', 'AlbumsController@getPositionData');

Route::post('/api/Album::get', 'AlbumController@get')->middleware('read');
Route::post('/api/Album::getPositionData', 'AlbumController@getPositionData')->middleware('read');
Route::post('/api/Album::getPublic', 'AlbumController@getPublic');
Route::post('/api/Album::add', 'AlbumController@add')->middleware('upload');
Route::post('/api/Album::setTitle', 'AlbumController@setTitle')->middleware('upload');
Route::post('/api/Album::setDescription', 'AlbumController@setDescription')->middleware('upload');
Route::post('/api/Album::setPublic', 'AlbumController@setPublic')->middleware('upload');
Route::post('/api/Album::delete', 'AlbumController@delete')->middleware('upload');
Route::post('/api/Album::merge', 'AlbumController@merge')->middleware('upload');
Route::post('/api/Album::move', 'AlbumController@move')->middleware('upload');
Route::post('/api/Album::setLicense', 'AlbumController@setLicense')->middleware('upload');
Route::get('/api/Album::getArchive', 'AlbumController@getArchive')->middleware('read');

Route::post('/api/Frame::getSettings', 'FrameController@getSettings');

Route::post('/api/Photo::get', 'PhotoController@get')->middleware('read');
Route::post('/api/Photo::getRandom', 'PhotoController@getRandom');
Route::post('/api/Photo::setTitle', 'PhotoController@setTitle')->middleware('upload');
Route::post('/api/Photo::setDescription', 'PhotoController@setDescription')->middleware('upload');
Route::post('/api/Photo::setStar', 'PhotoController@setStar')->middleware('upload');
Route::post('/api/Photo::setPublic', 'PhotoController@setPublic')->middleware('upload');
Route::post('/api/Photo::setAlbum', 'PhotoController@setAlbum')->middleware('upload');
Route::post('/api/Photo::setTags', 'PhotoController@setTags')->middleware('upload');
Route::post('/api/Photo::add', 'PhotoController@add')->middleware('upload');
Route::post('/api/Photo::delete', 'PhotoController@delete')->middleware('upload');
Route::post('/api/Photo::duplicate', 'PhotoController@duplicate')->middleware('upload');
Route::post('/api/Photo::setLicense', 'PhotoController@setLicense')->middleware('upload');
Route::get('/api/Photo::getArchive', 'PhotoController@getArchive')->middleware('read');
Route::get('/api/Photo::clearSymLink', 'PhotoController@clearSymLink')->middleware('admin');

Route::post('/api/Sharing::List', 'SharingController@listSharing')->middleware('upload');
Route::post('/api/Sharing::ListUser', 'SharingController@getUserList')->middleware('upload');
Route::post('/api/Sharing::Add', 'SharingController@add')->middleware('upload');
Route::post('/api/Sharing::Delete', 'SharingController@delete')->middleware('upload');

Route::post('/api/Settings::setLogin', 'SettingsController@setLogin');
Route::post('/api/Settings::setSorting', 'SettingsController@setSorting')->middleware('admin');
Route::post('/api/Settings::setLang', 'SettingsController@setLang')->middleware('admin');
Route::post('/api/Settings::setLayout', 'SettingsController@setLayout')->middleware('admin');
Route::post('/api/Settings::setPublicSearch', 'SettingsController@setPublicSearch')->middleware('admin');
Route::post('/api/Settings::setImageOverlay', 'SettingsController@setImageOverlay')->middleware('admin');
Route::post('/api/Settings::setDefaultLicense', 'SettingsController@setDefaultLicense')->middleware('admin');
Route::post('/api/Settings::setMapDisplay', 'SettingsController@setMapDisplay')->middleware('admin');
Route::post('/api/Settings::setMapDisplayPublic', 'SettingsController@setMapDisplayPublic')->middleware('admin');
Route::post('/api/Settings::setMapProvider', 'SettingsController@setMapProvider')->middleware('admin');
Route::post('/api/Settings::setMapIncludeSubalbums', 'SettingsController@setMapIncludeSubalbums')->middleware('admin');
Route::post('/api/Settings::setCSS', 'SettingsController@setCSS')->middleware('admin');
Route::post('/api/Settings::getAll', 'SettingsController@getAll')->middleware('admin');
Route::post('/api/Settings::saveAll', 'SettingsController@saveAll')->middleware('admin');
Route::post('/api/Settings::setOverlayType', 'SettingsController@setImageOverlayType')->middleware('admin');
Route::post('/api/Settings::setDropboxKey', 'SettingsController@setDropboxKey')->middleware('admin');

Route::post('/api/Import::url', 'ImportController@url')->middleware('upload');
Route::post('/api/Import::server', 'ImportController@server')->middleware('admin');

Route::post('/api/User::List', 'UserController@list')->middleware('upload');
Route::post('/api/User::Save', 'UserController@save')->middleware('admin');
Route::post('/api/User::Delete', 'UserController@delete')->middleware('admin');
Route::post('/api/User::Create', 'UserController@create')->middleware('admin');

Route::post('/api/Logs', 'LogController@display')->middleware('admin');
Route::post('/api/Logs::clearNoise', 'LogController@clearNoise')->middleware('admin');
Route::post('/api/Diagnostics', 'DiagnosticsController@get');

Route::get('/Logs', 'LogController@display')->middleware('admin');
Route::get('/api/Logs::clear', 'LogController@clear')->middleware('admin');
Route::get('/Diagnostics', 'DiagnosticsController@show');

Route::get('/Update', 'UpdateController@do')->middleware('admin');
Route::post('/api/Update::Apply', 'UpdateController@do')->middleware('admin');
Route::post('/api/Update::Check', 'UpdateController@check')->middleware('admin');

// unused
Route::post('/api/Logs::clear', 'LogController@clear')->middleware('admin');

Route::post('/api/search', 'SearchController@search');

Route::get('/{page}', 'PageController@page');
