<?php

namespace App\Http\Livewire;

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

Route::group(['layout' => ''], function () {
	Route::get('/livewire', Albums::class)->middleware(['installed', 'migrated']);
	Route::get('/livewire/Albums', Albums::class)->middleware(['installed', 'migrated']);
});
