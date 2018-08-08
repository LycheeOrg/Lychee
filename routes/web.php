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

Route::get('/', function () { return view('welcome'); })->name('home');
Route::get('/Logs',                         'LogController@list')->middleware('admin');
Route::get('/Logs:clear',                   'LogController@clear')->middleware('admin');
Route::get('/Diagnostics',                  'DiagnosticsController@show')->middleware('admin');

Route::post('/api/Session::init',           'SessionController@init');
Route::post('/api/Session::login',          'SessionController@login');
Route::post('/api/Session::logout',         'SessionController@logout');

Route::post('/api/Albums::get',             'AlbumsController@get');

Route::post('/api/Album::get',              'AlbumController@get')->middleware('AlbumPWCheck');
Route::post('/api/Album::getPublic',        'AlbumController@getPublic');
Route::post('/api/Album::add',              'AlbumController@add')->middleware('admin');
Route::post('/api/Album::setTitle',         'AlbumController@setTitle')->middleware('admin');
Route::post('/api/Album::setDescription',   'AlbumController@setDescription')->middleware('admin');
Route::post('/api/Album::setPublic',        'AlbumController@setPublic')->middleware('admin');
Route::post('/api/Album::delete',           'AlbumController@delete')->middleware('admin');
Route::post('/api/Album::merge',            'AlbumController@merge')->middleware('admin');

Route::post('/api/Photo::get',              'PhotoController@get')->middleware('AlbumPWCheck');
Route::post('/api/Photo::setTitle',         'PhotoController@setTitle')->middleware('admin');
Route::post('/api/Photo::setDescription',   'PhotoController@setDescription')->middleware('admin');
Route::post('/api/Photo::setStar',          'PhotoController@setStar')->middleware('admin');
Route::post('/api/Photo::setPublic',        'PhotoController@setPublic')->middleware('admin');
Route::post('/api/Photo::setAlbum',         'PhotoController@setAlbum')->middleware('admin');
Route::post('/api/Photo::setTags',          'PhotoController@setTags')->middleware('admin');
Route::post('/api/Photo::add',              'PhotoController@add')->middleware('admin');
Route::post('/api/Photo::delete',           'PhotoController@delete')->middleware('admin');
Route::post('/api/Photo::duplicate',        'PhotoController@duplicate')->middleware('admin');

Route::post('/api/Settings::setLogin',      'SettingsController@setLogin')->middleware('admin');
Route::post('/api/Settings::setSorting',    'SettingsController@setSorting')->middleware('admin');

Route::post('/api/search', function () { return 'false'; });
