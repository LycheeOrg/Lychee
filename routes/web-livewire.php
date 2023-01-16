<?php

namespace App\Http\Livewire;

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

if (config('app.livewire')) {
	Route::get('/livewire/{page?}/{albumId?}/{photoId?}', Index::class)
		->middleware(['installation:complete', 'migration:complete'])
		->name('livewire_index');
}
