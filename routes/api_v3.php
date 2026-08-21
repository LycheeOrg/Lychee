<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v3 Routes
|--------------------------------------------------------------------------
|
| Greenfield `/api/v3/...` surface (Feature 056). Coexists with v2; nothing
| here mutates or supersedes routes/api_v2.php.
|
*/

// Binary passthrough endpoint: the client's Accept/Content-Type headers are
// not expected to negotiate JSON, so the `api` group's JSON content-type
// enforcement (which every v2 JSON endpoint relies on) does not apply here.
// `json_errors` still forces every *error* response (404/401/403/422) to
// render as Lychee's standard JSON error body (FR-056-02), independent of
// what the client actually sent as its Accept header.
Route::get('/Asset/{album_id}/{photo_id}/{size_variant}', [Gallery\PhotoAssetController::class, 'show'])
	->withoutMiddleware(['accept_content_type:json', 'content_type:json'])
	->middleware('json_errors');
