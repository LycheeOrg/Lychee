<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

/**
 * Sitemap (Feature 117, API-117-01).
 * Runs in the `sitemap` group (configs, installation check): no session,
 * cookies, CSRF or HTML content check, so crawlers asking for XML are served
 * and the public cache header never travels with a session cookie (ADR-117-01).
 */
Route::get('/sitemap.xml', SitemapController::class)
	->middleware(['migration:complete'])
	->name('sitemap');
