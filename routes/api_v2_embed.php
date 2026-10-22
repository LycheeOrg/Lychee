<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

/**
 * Embed.
 * Public endpoints for embedding albums on external websites.
 * Note: These routes run in the `embed` group (configs, verification, locale,
 * CORS) and not in `api`: no session, cookies or CSRF, to allow cross-origin embedding.
 * IMPORTANT: More specific routes (stream) must come before generic routes ({album_id}) to avoid conflicts.
 */
Route::match(['GET', 'OPTIONS'], '/Embed/stream', [Gallery\EmbedController::class, 'getPublicStream'])
	->middleware([
		'cache_control:900', // 15 minutes cache (longer for stream due to expensive query)
		'throttle:30,1', // 30 requests per minute per IP (more restrictive)
	])
	->name('embed.stream');

Route::match(['GET', 'OPTIONS'], '/Embed/{album_id}', [Gallery\EmbedController::class, 'getAlbum'])
	->middleware([
		'cache_control:300', // 5 minutes cache (reduced from 1 hour for more frequent updates)
		'throttle:120,1', // 120 requests per minute per IP
	])
	->name('embed.album');
