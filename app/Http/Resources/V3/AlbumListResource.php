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
 * Response body of `GET /api/v3/Albums` (Feature 057, DO-057-01).
 *
 * Struct-of-Arrays per ADR-0009: every album-indexed field is a parallel,
 * index-aligned array rather than an array of per-album objects. The
 * complete curated result set is always returned in one response — never
 * paginated (NFR-057-02) — ordered by `albums._lft` ascending, so a client
 * can reconstruct a valid tree in one pass without a second request.
 */
#[TypeScript()]
class AlbumListResource extends Data
{
	/** @var string[] */
	public array $ids;
	/** @var string[] */
	public array $titles;
	/** @var int[] */
	public array $lft;
	/** @var int[] */
	public array $rgt;
	/** @var (string|null)[] */
	public array $cover_ids;
	/** @var (string|null)[]|null `null` when `with_parent_id=false`; otherwise index-aligned, `null` per-entry for a root album (never omitted). */
	public ?array $parent_ids;
	public ?AlbumListBulkEditFieldsResource $bulk_edit;

	/**
	 * @param string[]             $ids
	 * @param string[]             $titles
	 * @param int[]                $lft
	 * @param int[]                $rgt
	 * @param (string|null)[]      $cover_ids
	 * @param (string|null)[]|null $parent_ids
	 */
	public function __construct(
		array $ids,
		array $titles,
		array $lft,
		array $rgt,
		array $cover_ids,
		?array $parent_ids,
		?AlbumListBulkEditFieldsResource $bulk_edit = null,
	) {
		$this->ids = $ids;
		$this->titles = $titles;
		$this->lft = $lft;
		$this->rgt = $rgt;
		$this->cover_ids = $cover_ids;
		$this->parent_ids = $parent_ids;
		$this->bulk_edit = $bulk_edit;
	}
}
