<?php

namespace Opcodes\LogViewer\Http\Controllers;

use Illuminate\Support\Facades\Route;

// Catch all route
Route::get('/{view?}', IndexController::class)
	->where('view', '(.*)')
	->name('log-viewer.index');
