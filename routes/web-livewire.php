<?php

namespace App\Livewire\Components\Pages;

use App\Assets\Features;
use App\Enum\OauthProvidersType;
use App\Http\Controllers\Oauth;
use App\Http\Controllers\RedirectController;
use App\Livewire\Components\Pages\Gallery\Album;
use App\Livewire\Components\Pages\Gallery\Albums;
use App\Livewire\Components\Pages\Gallery\Search;
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
Route::prefix(Features::whenConst('livewire', '', 'livewire'))
	->group(function () {
		Route::get('/diagnostics', Diagnostics::class)->name('diagnostics');
	});

Route::middleware(['installation:complete', 'migration:complete'])
	->group(function () {
		Route::prefix(Features::whenConst('livewire', '', 'livewire'))
		->group(function () {
			// Oauth routes.
			Route::get('/auth/{provider}/redirect', [Oauth::class, 'redirected'])->whereIn('provider', OauthProvidersType::values());
			Route::get('/auth/{provider}/authenticate', [Oauth::class, 'authenticate'])->name('oauth-authenticate')->whereIn('provider', OauthProvidersType::values());
			Route::get('/auth/{provider}/register', [Oauth::class, 'register'])->name('oauth-register')->whereIn('provider', OauthProvidersType::values());

			Route::get('/landing', Landing::class)->name('landing');
			Route::get('/all-settings', AllSettings::class)->name('all-settings');
			Route::get('/settings', Settings::class)->name('settings');
			Route::get('/profile', Profile::class)->name('profile');
			Route::get('/users', Users::class)->name('users');
			Route::get('/sharing', Sharing::class)->name('sharing');
			Route::get('/jobs', Jobs::class)->name('jobs');
			Route::get('/map/{albumId?}', Map::class)->name('livewire-map');
			Route::get('/frame/{albumId?}', Frame::class)->name('livewire-frame');
			Route::get('/gallery', Albums::class)->name('livewire-gallery');
			Route::get('/search/{albumId?}', Search::class)->name('livewire-search');
			Route::get('/gallery/{albumId}/', Album::class)->name('livewire-gallery-album');
			Route::get('/gallery/{albumId}/{photoId}', Album::class)->name('livewire-gallery-photo');
			Route::get('/', [RedirectController::class, 'view'])->name('livewire-index');
		});
	});

