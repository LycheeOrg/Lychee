<?php

namespace App\Livewire;

use App\Livewire\Components\Pages\AllSettings;
use App\Livewire\Components\Pages\Diagnostics;
use App\Livewire\Components\Pages\Gallery\Album;
use App\Livewire\Components\Pages\Gallery\Albums;
use App\Livewire\Components\Pages\Map;
use App\Livewire\Components\Pages\Gallery\Photo;
use App\Livewire\Components\Pages\Jobs;
use App\Livewire\Components\Pages\Landing;
use App\Livewire\Components\Pages\Profile;
use App\Livewire\Components\Pages\Settings;
use App\Livewire\Components\Pages\Sharing;
use App\Livewire\Components\Pages\Users;
use App\Models\Configs;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

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

if (config('app.force_https')) {
	URL::forceScheme('https');
}

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
			Route::get('/map', Map::class)->name('livewire-map');
			Route::get('/gallery', Albums::class)->name('livewire-gallery');
			Route::get('/gallery/{albumId}/', Album::class)->name('livewire-gallery-album');
			Route::get('/gallery/{albumId}/{photoId}', Photo::class)->name('livewire-gallery-photo');
			Route::get('/', function () {
				return redirect(Configs::getValueAsBool('landing_page_enable') ? route('landing') : route('livewire-gallery'));
			})->name('livewire-index');
		});
	});

