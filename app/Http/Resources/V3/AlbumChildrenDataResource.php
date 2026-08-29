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
 * Response body of `GET /api/v3/Albums/{album_id}/children` (Feature 061,
 * DO-061-08).
 *
 * Struct-of-Arrays per ADR-0009: one whole-album-at-once body (no windowed
 * pagination), built from a single flat, join-free `toBase()` query — every
 * field is a plain column already on the `albums`/`base_albums` row
 * (NFR-061-07). `bucket_ids[i]` is each child's own `bucket_id`
 * (`"unknown"` substituted for `null`) — the join key a client uses to
 * place a tile under the buckets endpoint's matching sticky-header section
 * (FR-061-17). No thumbnail media `type`/blur `placeholder` field — those
 * require a join this endpoint deliberately never adds (Non-Goals).
 */
#[TypeScript()]
class AlbumChildrenDataResource extends Data
{
	/**
	 * @param string[]        $ids
	 * @param string[]        $titles
	 * @param string[]        $descriptions
	 * @param (string|null)[] $cover_ids
	 * @param string[]        $bucket_ids
	 * @param bool[]          $is_password_requireds
	 * @param bool[]          $is_nsfws
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
		public array $is_password_requireds,
		public array $is_nsfws,
		public array $has_subalbums,
		public array $num_photos,
		public array $num_subalbums,
		public array $created_ats,
		public array $min_taken_ats,
		public array $max_taken_ats,
	) {
	}
}
