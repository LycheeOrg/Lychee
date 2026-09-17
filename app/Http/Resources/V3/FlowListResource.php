<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\V3;

use App\Http\Resources\Models\AlbumStatisticsResource;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Response body of `GET /api/v3/Flow` (Feature 068, API-068-01).
 *
 * Struct-of-Arrays per ADR-0009: every album-indexed field is a parallel,
 * index-aligned array rather than an array of per-album objects. The
 * complete result set for the caller's current Flow scope/strategy is
 * always returned in one response — never paginated (FR-068-01, mirrors
 * `AlbumListResource`'s own unpaginated precedent, not a Timeline-style
 * aggregated bucket tier) — ordered exactly like `App\Actions\Albums\Flow::do()`.
 *
 * Deliberately excludes any nested per-album photo data: each card's photo
 * preview is fetched separately and lazily via the existing `ratios` tier
 * (`GET /Albums/{album_id}/Photos?limit=12`) once that card scrolls into
 * view (FR-068-03/04), not embedded here.
 */
#[TypeScript()]
class FlowListResource extends Data
{
	/** @var string[] */
	public array $ids;
	/** @var string[] */
	public array $titles;
	/** @var string[] rendered HTML (Markdown-converted), mirrors `FlowItemResource::$description` */
	public array $descriptions;
	/** @var (string|null)[] */
	public array $cover_ids;
	/** @var (string|null)[] `null` for a guest viewer, mirrors `FlowItemResource::$owner_name` */
	public array $owner_names;
	/** @var bool[] */
	public array $is_nsfws;
	/** @var int[] */
	public array $num_photos;
	/** @var int[] */
	public array $num_children;
	/** @var (string|null)[] */
	public array $min_max_texts;
	/** @var string[] */
	public array $published_created_ats;
	/** @var string[] */
	public array $diff_published_created_ats;
	/** @var (AlbumStatisticsResource|null)[] `null` per-entry when the viewer cannot read that album's metrics */
	public array $statistics;

	/**
	 * @param string[]                         $ids
	 * @param string[]                         $titles
	 * @param string[]                         $descriptions
	 * @param (string|null)[]                  $cover_ids
	 * @param (string|null)[]                  $owner_names
	 * @param bool[]                           $is_nsfws
	 * @param int[]                            $num_photos
	 * @param int[]                            $num_children
	 * @param (string|null)[]                  $min_max_texts
	 * @param string[]                         $published_created_ats
	 * @param string[]                         $diff_published_created_ats
	 * @param (AlbumStatisticsResource|null)[] $statistics
	 */
	public function __construct(
		array $ids,
		array $titles,
		array $descriptions,
		array $cover_ids,
		array $owner_names,
		array $is_nsfws,
		array $num_photos,
		array $num_children,
		array $min_max_texts,
		array $published_created_ats,
		array $diff_published_created_ats,
		array $statistics,
	) {
		$this->ids = $ids;
		$this->titles = $titles;
		$this->descriptions = $descriptions;
		$this->cover_ids = $cover_ids;
		$this->owner_names = $owner_names;
		$this->is_nsfws = $is_nsfws;
		$this->num_photos = $num_photos;
		$this->num_children = $num_children;
		$this->min_max_texts = $min_max_texts;
		$this->published_created_ats = $published_created_ats;
		$this->diff_published_created_ats = $diff_published_created_ats;
		$this->statistics = $statistics;
	}
}
