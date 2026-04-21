<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Admin;

use App\Enum\AspectRatioType;
use App\Enum\ColumnSortingAlbumType;
use App\Enum\ColumnSortingPhotoType;
use App\Enum\LicenseType;
use App\Enum\OrderSortingType;
use App\Enum\PhotoLayoutType;
use App\Enum\TimelineAlbumGranularity;
use App\Enum\TimelinePhotoGranularity;
use Carbon\Carbon;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * API resource for a single album row in the Bulk Album Edit admin page.
 *
 * _lft and _rgt are included so the Vue frontend can compute depth
 * in a single O(n) linear pass (Q-034-02 → Option B).
 * No depth field is computed server-side.
 */
#[TypeScript()]
class BulkAlbumResource extends Data
{
	public string $id;
	public string $title;
	public int $owner_id;
	public string $owner_name;
	public ?string $description;
	public ?string $copyright;
	public LicenseType $license;
	public ?PhotoLayoutType $photo_layout;
	public ?ColumnSortingPhotoType $photo_sorting_col;
	public ?OrderSortingType $photo_sorting_order;
	public ?ColumnSortingAlbumType $album_sorting_col;
	public ?OrderSortingType $album_sorting_order;
	public ?AspectRatioType $album_thumb_aspect_ratio;
	public ?TimelineAlbumGranularity $album_timeline;
	public ?TimelinePhotoGranularity $photo_timeline;
	public bool $is_nsfw;
	public int $_lft;
	public int $_rgt;
	// Visibility / protection policy fields (false when album has no public access_permissions row)
	public bool $is_public;
	public bool $is_link_required;
	public bool $grants_full_photo_access;
	public bool $grants_download;
	public bool $grants_upload;
	public Carbon $created_at;

	public function __construct(
		string $id,
		string $title,
		int $owner_id,
		string $owner_name,
		?string $description,
		?string $copyright,
		LicenseType $license,
		?PhotoLayoutType $photo_layout,
		?ColumnSortingPhotoType $photo_sorting_col,
		?OrderSortingType $photo_sorting_order,
		?ColumnSortingAlbumType $album_sorting_col,
		?OrderSortingType $album_sorting_order,
		?AspectRatioType $album_thumb_aspect_ratio,
		?TimelineAlbumGranularity $album_timeline,
		?TimelinePhotoGranularity $photo_timeline,
		bool $is_nsfw,
		int $_lft,
		int $_rgt,
		bool $is_public,
		bool $is_link_required,
		bool $grants_full_photo_access,
		bool $grants_download,
		bool $grants_upload,
		Carbon $created_at,
	) {
		$this->id = $id;
		$this->title = $title;
		$this->owner_id = $owner_id;
		$this->owner_name = $owner_name;
		$this->description = $description;
		$this->copyright = $copyright;
		$this->license = $license;
		$this->photo_layout = $photo_layout;
		$this->photo_sorting_col = $photo_sorting_col;
		$this->photo_sorting_order = $photo_sorting_order;
		$this->album_sorting_col = $album_sorting_col;
		$this->album_sorting_order = $album_sorting_order;
		$this->album_thumb_aspect_ratio = $album_thumb_aspect_ratio;
		$this->album_timeline = $album_timeline;
		$this->photo_timeline = $photo_timeline;
		$this->is_nsfw = $is_nsfw;
		$this->_lft = $_lft;
		$this->_rgt = $_rgt;
		$this->is_public = $is_public;
		$this->is_link_required = $is_link_required;
		$this->grants_full_photo_access = $grants_full_photo_access;
		$this->grants_download = $grants_download;
		$this->grants_upload = $grants_upload;
		$this->created_at = $created_at;
	}
}
