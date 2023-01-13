<?php

namespace App\Http\Livewire;

use App\Http\Livewire\Pages\Fullpage;
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

if (config('app.livewire')) {
	Route::get('/livewire/{albumId?}/{photoId?}', Fullpage::class)->middleware(['installation:complete', 'migration:complete'])->name('livewire_index');
}
