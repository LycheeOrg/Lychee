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

// Unpaginated Struct-of-Arrays listing for the Flow feed (Feature 068,
// API-068-01) - each album is its own "bucket equivalent," loaded
// whole-scope in one request; per-card photo previews are fetched
// separately via the existing `/Albums/{album_id}/Photos?limit=N` route
// below, coexisting with the v2 `GET /Flow` route (routes/api_v2.php)
// behind `is_struct_of_array_enabled`.
Route::get('/Flow', [Gallery\FlowListController::class, 'index']);

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

// Photo virtual-scroll backend, same gating mechanism as the album family
// above. Distinct literal-segment routes from
// `/Albums/{album_id}/buckets`/`/rights` above — Laravel disambiguates
// same-position literal segments, no collision.
Route::get('/Albums/{album_id}/Photos', [Gallery\AlbumListing\PhotoChildrenController::class, 'index']);
Route::get('/Albums/{album_id}/Photos/buckets', [Gallery\AlbumListing\PhotoChildrenController::class, 'buckets']);
Route::get('/Albums/{album_id}/Photos/details', [Gallery\AlbumListing\PhotoChildrenController::class, 'details']);

// Search's Struct-of-Arrays API (Feature 069), coexisting with the v2
// `/Search` route (routes/api_v2.php) behind the same
// `is_struct_of_array_enabled` flag — v7 and `SpotlightSearch.vue` keep using
// v2. A dedicated family rather than a pseudo-album on the `/Albums/...`
// routes (Q-069-01): search is not an album scope, and its result has an album
// half as well as a photo half. Literal `/Photos/details` and `/albums/rights`
// segments are registered before their parents, as elsewhere in this file.
// Deliberately no `/Search/Photos/buckets` — search is the one photo tier with
// no bucket level (spec.md NG1, ADR-0010).
Route::get('/Search/Photos', [Gallery\SearchListingController::class, 'photos']);
Route::get('/Search/Photos/details', [Gallery\SearchListingController::class, 'details']);
Route::get('/Search/albums', [Gallery\SearchListingController::class, 'albums']);
Route::get('/Search/albums/rights', [Gallery\SearchListingController::class, 'albumRights']);

// Map's bucket-tiered API (Feature 067), coexisting with the v2 `/Map` route
// (routes/api_v2.php) behind the same `is_struct_of_array_enabled` flag.
Route::get('/Map/buckets', [Gallery\MapListingController::class, 'buckets']);
Route::get('/Map/Photos', [Gallery\MapListingController::class, 'photos']);
Route::get('/Map/tracks', [Gallery\MapListingController::class, 'tracks']);
