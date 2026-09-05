<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\V3;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Response body of `GET /api/v3/Albums/{album_id}` and
 * `GET /api/v3/Albums/root` — despite the "children" framing below (this
 * class predates root's own reuse of it), it serves both tiers alike.
 *
 * Struct-of-Arrays per ADR-0009: one whole-album-at-once body (no windowed
 * pagination), built from a single flat `toBase()` query with zero joins
 * beyond {@see \App\Policies\AlbumQueryPolicy::applyVisibilityFilter()}'s own
 * plus one small additional left join for the album's public access grant
 * — every field is a plain column already on the
 * `albums`/`base_albums`/`access_permissions` row. `bucket_ids[i]`
 * is each child's own `bucket_id` (`"unknown"` substituted for `null`) — the
 * join key a client uses to place a tile under the buckets endpoint's
 * matching sticky-header section. `is_publics[i]`/
 * `is_link_requireds[i]` reflect the album's own public/anonymous grant,
 * independent of the requesting viewer's identity — not to be confused with
 * `is_password_requireds[i]`, which reflects the *viewer's* effective access.
 * No thumbnail media `type`/blur `placeholder` field — those
 * require a join this endpoint deliberately never adds (Non-Goals).
 *
 * `owner_ids[]` is additive — populated for both the
 * sub-album tier and the root tier; for root's `scope=shared`, `bucket_ids[i]`
 * additionally carries the row's own `owner_id` rather than a date/title
 * bucket.
 */
#[TypeScript()]
class AlbumDataResource extends Data
{
	/**
	 * @param string[]        $ids
	 * @param string[]        $titles
	 * @param string[]        $descriptions
	 * @param (string|null)[] $cover_ids
	 * @param string[]        $bucket_ids
	 * @param string[]        $owner_ids
	 * @param bool[]          $is_password_requireds
	 * @param bool[]          $is_nsfws
	 * @param bool[]          $is_pinneds
	 * @param bool[]          $is_publics
	 * @param bool[]          $is_link_requireds
	 * @param bool[]          $has_subalbums
	 * @param int[]           $num_photos
	 * @param int[]           $num_subalbums
	 * @param string[]        $created_ats
	 * @param (string|null)[] $min_taken_ats
	 * @param (string|null)[] $max_taken_ats
	 */
	public function __construct(
		public array $ids,
		public array $titles,
		public array $descriptions,
		public array $cover_ids,
		public array $bucket_ids,
		public array $owner_ids,
		public array $is_password_requireds,
		public array $is_nsfws,
		public array $is_pinneds,
		public array $is_publics,
		public array $is_link_requireds,
		public array $has_subalbums,
		public array $num_photos,
		public array $num_subalbums,
		public array $created_ats,
		public array $min_taken_ats,
		public array $max_taken_ats,
	) {
	}
}
