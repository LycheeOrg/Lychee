<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\V3;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Response body of `GET /api/v3/Albums/{album_id}/rights` and
 * `GET /api/v3/Albums/root/rights` — the raw permission signals a
 * right-click menu on a selection of albums needs, not the fully-combined
 * `can_edit`/`can_download` booleans (Non-Goals): `owner_id`/`can_delete_children`/
 * `can_move_children` are whole-response (uniform across every direct
 * child, since both checks key off `parent_id`, which is `album_id` itself
 * for every direct child); `grants_edit`/`grants_download`/`grants_move` are
 * per-child, index-aligned with `ids`. Feature 072 (Q-072-09): the move grant
 * covers an album's content, so `can_move_children` (moving a child) comes
 * from the grant on the parent, while `grants_move[i]` (moving child i's own
 * content, as merge does) comes from the grant on the child. `grants_upload`/`grants_full_photo_access` and
 * any combined `can_*` field are deliberately not transmitted — neither
 * underlying right is offered by the right-click menu this endpoint serves
 * (Non-Goals).
 *
 * `owner_id` is widened to `string|Optional`: root
 * has no single "parent album" whose grants this endpoint checks
 * (heterogeneous ownership either way), so the value is always absent for
 * root's response, for **both** `own` and `shared` scope —
 * `Optional` omits the key from the JSON payload entirely rather than
 * serializing a useless `"owner_id": null`. The sub-album and
 * `TagAlbum`/`PersonAlbum`-matching tiers keep emitting a real value
 * unchanged.
 */
#[TypeScript()]
class AlbumRightsResource extends Data
{
	/**
	 * @param string[] $ids
	 * @param bool[]   $grants_edit
	 * @param bool[]   $grants_download
	 * @param bool[]   $grants_move
	 */
	public function __construct(
		public string|Optional $owner_id,
		public bool $can_delete_children,
		public bool $can_move_children,
		public array $ids,
		public array $grants_edit,
		public array $grants_download,
		public array $grants_move,
	) {
	}
}
