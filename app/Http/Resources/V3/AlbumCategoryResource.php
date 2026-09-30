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
 * Response body shared by `GET /api/v3/Albums/smart`, `/persons`, `/tags`, `/pinned`.
 *
 * Struct-of-Arrays per ADR-0009: a flat, un-bucketed, minimum-viable shape
 * (`ids`/`titles`/`cover_ids`/`cover_ids_2`/`cover_ids_3`/`owner_ids`,
 * the last two being Feature 075's rank-2/3 side covers) — these categories are
 * curated/bounded by an admin's tag/person taxonomy or explicit pin action,
 * not by photo volume, so no bucket/virtual-scroll tier is warranted.
 * `owner_ids[i]` lets a client render a "shared by X" label per row even
 * though the server never pre-groups these categories by owner —
 * `smart` albums have no real owner and always report `"0"`.
 */
#[TypeScript()]
class AlbumCategoryResource extends Data
{
	/**
	 * @param string[]        $ids
	 * @param string[]        $titles
	 * @param (string|null)[] $cover_ids
	 * @param (string|null)[] $cover_ids_2
	 * @param (string|null)[] $cover_ids_3
	 * @param string[]        $owner_ids
	 */
	public function __construct(
		public array $ids,
		public array $titles,
		public array $cover_ids,
		public array $cover_ids_2,
		public array $cover_ids_3,
		public array $owner_ids,
	) {
	}
}
