<?php

namespace App\Livewire\Components\Pages;

use App\Livewire\Components\Pages\Gallery\Album;
use App\Livewire\Components\Pages\Gallery\Albums;
use App\Livewire\Components\Pages\Gallery\Search;
use App\Models\Configs;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Livewire Routes
|--------------------------------------------------------------------------
|
| Here is where you can register livewire routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::middleware(['installation:complete', 'migration:complete'])
	->group(function () {
		Route::prefix(config('app.livewire') === true ? '' : 'livewire')
		->group(function () {
			Route::get('/landing', Landing::class)->name('landing');
			Route::get('/all-settings', AllSettings::class)->name('all-settings');
			Route::get('/settings', Settings::class)->name('settings');
			Route::get('/profile', Profile::class)->name('profile');
			Route::get('/users', Users::class)->name('users');
			Route::get('/sharing', Sharing::class)->name('sharing');
			Route::get('/jobs', Jobs::class)->name('jobs');
			Route::get('/diagnostics', Diagnostics::class)->name('diagnostics');
			Route::get('/map/{albumId?}', Map::class)->name('livewire-map');
			Route::get('/frame/{albumId?}', Frame::class)->name('livewire-frame');
			Route::get('/gallery', Albums::class)->name('livewire-gallery');
			Route::get('/search/{albumId?}', Search::class)->name('livewire-search');
			Route::get('/gallery/{albumId}/', Album::class)->name('livewire-gallery-album');
			Route::get('/gallery/{albumId}/{photoId}', Album::class)->name('livewire-gallery-photo');
			Route::get('/', function () {
				return redirect(Configs::getValueAsBool('landing_page_enable') ? route('landing') : route('livewire-gallery'));
			})->name('livewire-index');
		});
	});

