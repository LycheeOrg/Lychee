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
 * Response body of `GET /api/v3/Albums/tags/rights` —
 * a flat tag-album catalogue has no parent-child relationship
 * to check, so unlike {@see AlbumRightsResource} there is no
 * `can_delete_children`/`can_move_children` concept, and no whole-response
 * `owner_id` (each tag album has its own, carried per-row via the sibling
 * `/Albums/tags` listing's `owner_ids[]` instead). `grants_delete[]` is
 * additionally meaningful here — tag albums are user-editable/deletable
 * objects with real per-row grants, unlike root/sub-album children.
 */
#[TypeScript()]
class AlbumCategoryRightsResource extends Data
{
	/**
	 * @param string[] $ids
	 * @param bool[]   $grants_edit
	 * @param bool[]   $grants_download
	 * @param bool[]   $grants_delete
	 */
	public function __construct(
		public array $ids,
		public array $grants_edit,
		public array $grants_download,
		public array $grants_delete,
	) {
	}
}
