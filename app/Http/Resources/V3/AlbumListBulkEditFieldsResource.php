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
 * Struct-of-Arrays block returned by `GET /api/v3/Albums` when
 * `for_bulk_edit=true` (Feature 057, DO-057-02).
 *
 * Full field parity with {@see \App\Http\Resources\Admin\BulkAlbumResource}
 * (the Bulk Album Edit admin page's per-album resource), so a later,
 * separate frontend feature can source that page's row data from this
 * endpoint as a drop-in. Every array is index-aligned to
 * {@see AlbumListResource}'s own arrays (same album at the same index).
 */
#[TypeScript()]
class AlbumListBulkEditFieldsResource extends Data
{
	/** @var int[] */
	public array $owner_ids;
	/** @var string[] */
	public array $owner_names;
	/** @var (string|null)[] */
	public array $descriptions;
	/** @var (string|null)[] */
	public array $copyrights;
	/** @var string[] */
	public array $licenses;
	/** @var (string|null)[] */
	public array $photo_layouts;
	/** @var (string|null)[] */
	public array $photo_sorting_cols;
	/** @var (string|null)[] */
	public array $photo_sorting_orders;
	/** @var (string|null)[] */
	public array $album_sorting_cols;
	/** @var (string|null)[] */
	public array $album_sorting_orders;
	/** @var (string|null)[] */
	public array $album_thumb_aspect_ratios;
	/** @var (string|null)[] */
	public array $album_timelines;
	/** @var (string|null)[] */
	public array $photo_timelines;
	/** @var bool[] */
	public array $is_nsfws;
	/** @var bool[] */
	public array $is_publics;
	/** @var bool[] */
	public array $is_link_requireds;
	/** @var bool[] */
	public array $grants_full_photo_accesses;
	/** @var bool[] */
	public array $grants_downloads;
	/** @var bool[] */
	public array $grants_uploads;
	/** @var string[] ISO 8601 */
	public array $created_ats;

	/**
	 * @param int[]           $owner_ids
	 * @param string[]        $owner_names
	 * @param (string|null)[] $descriptions
	 * @param (string|null)[] $copyrights
	 * @param string[]        $licenses
	 * @param (string|null)[] $photo_layouts
	 * @param (string|null)[] $photo_sorting_cols
	 * @param (string|null)[] $photo_sorting_orders
	 * @param (string|null)[] $album_sorting_cols
	 * @param (string|null)[] $album_sorting_orders
	 * @param (string|null)[] $album_thumb_aspect_ratios
	 * @param (string|null)[] $album_timelines
	 * @param (string|null)[] $photo_timelines
	 * @param bool[]          $is_nsfws
	 * @param bool[]          $is_publics
	 * @param bool[]          $is_link_requireds
	 * @param bool[]          $grants_full_photo_accesses
	 * @param bool[]          $grants_downloads
	 * @param bool[]          $grants_uploads
	 * @param string[]        $created_ats
	 */
	public function __construct(
		array $owner_ids,
		array $owner_names,
		array $descriptions,
		array $copyrights,
		array $licenses,
		array $photo_layouts,
		array $photo_sorting_cols,
		array $photo_sorting_orders,
		array $album_sorting_cols,
		array $album_sorting_orders,
		array $album_thumb_aspect_ratios,
		array $album_timelines,
		array $photo_timelines,
		array $is_nsfws,
		array $is_publics,
		array $is_link_requireds,
		array $grants_full_photo_accesses,
		array $grants_downloads,
		array $grants_uploads,
		array $created_ats,
	) {
		$this->owner_ids = $owner_ids;
		$this->owner_names = $owner_names;
		$this->descriptions = $descriptions;
		$this->copyrights = $copyrights;
		$this->licenses = $licenses;
		$this->photo_layouts = $photo_layouts;
		$this->photo_sorting_cols = $photo_sorting_cols;
		$this->photo_sorting_orders = $photo_sorting_orders;
		$this->album_sorting_cols = $album_sorting_cols;
		$this->album_sorting_orders = $album_sorting_orders;
		$this->album_thumb_aspect_ratios = $album_thumb_aspect_ratios;
		$this->album_timelines = $album_timelines;
		$this->photo_timelines = $photo_timelines;
		$this->is_nsfws = $is_nsfws;
		$this->is_publics = $is_publics;
		$this->is_link_requireds = $is_link_requireds;
		$this->grants_full_photo_accesses = $grants_full_photo_accesses;
		$this->grants_downloads = $grants_downloads;
		$this->grants_uploads = $grants_uploads;
		$this->created_ats = $created_ats;
	}
}
