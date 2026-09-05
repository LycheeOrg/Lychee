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
| Greenfield `/api/v3/...` surface. Coexists with v2; nothing here mutates
| or supersedes routes/api_v2.php.
|
*/

// Binary passthrough endpoint: the client's Accept/Content-Type headers are
// not expected to negotiate JSON, so the `api` group's JSON content-type
// enforcement (which every v2 JSON endpoint relies on) does not apply here.
// `json_errors` still forces every *error* response (404/401/403/422) to
// render as Lychee's standard JSON error body, independent of
// what the client actually sent as its Accept header.
Route::get('/Asset/{album_id}/{photo_id}/{size_variant}', [Gallery\PhotoAssetController::class, 'show'])
	->withoutMiddleware(['accept_content_type:json', 'content_type:json'])
	->middleware('json_errors');

// Struct-of-Arrays JSON collection endpoint (ADR-0009).
Route::get('/Albums', [Gallery\AlbumListController::class, 'index']);

// Flat Struct-of-Arrays listing of album access permissions for the bulk-share page.
Route::get('/Albums::accessPermissions', [Gallery\AlbumAccessPermissionListController::class, 'index']);

// Album virtual-scroll backend, gated by features.struct-of-array at each
// request's FormRequest::authorize() (exposed to the frontend as
// modules.is_struct_of_array_enabled). Literal segments (`root`, `smart`,
// `persons`, `tags`, `pinned`) are registered ahead of this `{album_id}`
// family so Laravel never matches the wildcard first.
Route::get('/Albums/root', [Gallery\AlbumListing\AlbumRootController::class, 'index']);
Route::get('/Albums/root/buckets', [Gallery\AlbumListing\AlbumRootController::class, 'buckets']);
Route::get('/Albums/root/rights', [Gallery\AlbumListing\AlbumRootController::class, 'rights']);
Route::get('/Albums/smart', [Gallery\AlbumListing\AlbumSmartController::class, 'smart']);
Route::get('/Albums/persons', [Gallery\AlbumListing\AlbumPersonController::class, 'persons']);
Route::get('/Albums/tags', [Gallery\AlbumListing\AlbumTagController::class, 'tags']);
Route::get('/Albums/tags/rights', [Gallery\AlbumListing\AlbumTagController::class, 'tagsRights']);
Route::get('/Albums/pinned', [Gallery\AlbumListing\AlbumPinnedController::class, 'pinned']);

Route::get('/Albums/{album_id}', [Gallery\AlbumListing\AlbumChildrenController::class, 'index']);
Route::get('/Albums/{album_id}/buckets', [Gallery\AlbumListing\AlbumChildrenController::class, 'buckets']);
Route::get('/Albums/{album_id}/rights', [Gallery\AlbumListing\AlbumChildrenController::class, 'rights']);
