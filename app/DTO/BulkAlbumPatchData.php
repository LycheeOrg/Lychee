<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO;

use App\Enum\AspectRatioType;
use App\Enum\ColumnSortingAlbumType;
use App\Enum\ColumnSortingPhotoType;
use App\Enum\LicenseType;
use App\Enum\OrderSortingType;
use App\Enum\PhotoLayoutType;
use App\Enum\TimelineAlbumGranularity;
use App\Enum\TimelinePhotoGranularity;

/**
 * Typed payload for a bulk partial-update of album metadata and visibility.
 *
 * Only fields that were actually present in the incoming request are considered
 * "set".  Use {@see has()} to distinguish "not sent" from "sent as null"
 * (which clears the field).
 *
 * Boolean fields (is_nsfw, is_public, …) are required to be non-null when
 * present, so their types are non-nullable; absent fields remain null here
 * and are guarded by {@see has()}.
 */
class BulkAlbumPatchData
{
	/** @var string[] Names of fields that were present in the request. */
	private array $present_fields;

	/** @var string[] */
	public array $album_ids;

	public ?string $description;
	public ?string $copyright;
	public ?LicenseType $license;
	public ?PhotoLayoutType $photo_layout;
	public ?ColumnSortingPhotoType $photo_sorting_col;
	public ?OrderSortingType $photo_sorting_order;
	public ?ColumnSortingAlbumType $album_sorting_col;
	public ?OrderSortingType $album_sorting_order;
	public ?AspectRatioType $album_thumb_aspect_ratio;
	public ?TimelineAlbumGranularity $album_timeline;
	public ?TimelinePhotoGranularity $photo_timeline;
	// Boolean fields: null here means "not present in the request"
	public ?bool $is_nsfw;
	public ?bool $is_public;
	public ?bool $is_link_required;
	public ?bool $grants_full_photo_access;
	public ?bool $grants_download;
	public ?bool $grants_upload;

	/**
	 * @param string[]                  $album_ids
	 * @param string[]                  $present_fields           names of optional fields that were included in the request
	 * @param ?string                   $description
	 * @param ?string                   $copyright
	 * @param ?LicenseType              $license
	 * @param ?PhotoLayoutType          $photo_layout
	 * @param ?ColumnSortingPhotoType   $photo_sorting_col
	 * @param ?OrderSortingType         $photo_sorting_order
	 * @param ?ColumnSortingAlbumType   $album_sorting_col
	 * @param ?OrderSortingType         $album_sorting_order
	 * @param ?AspectRatioType          $album_thumb_aspect_ratio
	 * @param ?TimelineAlbumGranularity $album_timeline
	 * @param ?TimelinePhotoGranularity $photo_timeline
	 * @param ?bool                     $is_nsfw
	 * @param ?bool                     $is_public
	 * @param ?bool                     $is_link_required
	 * @param ?bool                     $grants_full_photo_access
	 * @param ?bool                     $grants_download
	 * @param ?bool                     $grants_upload
	 */
	public function __construct(
		array $album_ids,
		array $present_fields,
		?string $description,
		?string $copyright,
		?LicenseType $license,
		?PhotoLayoutType $photo_layout,
		?ColumnSortingPhotoType $photo_sorting_col,
		?OrderSortingType $photo_sorting_order,
		?ColumnSortingAlbumType $album_sorting_col,
		?OrderSortingType $album_sorting_order,
		?AspectRatioType $album_thumb_aspect_ratio,
		?TimelineAlbumGranularity $album_timeline,
		?TimelinePhotoGranularity $photo_timeline,
		?bool $is_nsfw,
		?bool $is_public,
		?bool $is_link_required,
		?bool $grants_full_photo_access,
		?bool $grants_download,
		?bool $grants_upload,
	) {
		$this->album_ids = $album_ids;
		$this->present_fields = $present_fields;
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
		$this->is_public = $is_public;
		$this->is_link_required = $is_link_required;
		$this->grants_full_photo_access = $grants_full_photo_access;
		$this->grants_download = $grants_download;
		$this->grants_upload = $grants_upload;
	}

	/**
	 * Returns true if the named field was present in the original request.
	 *
	 * A field can be present-but-null (meaning "clear this value") or
	 * present-with-a-value.  A field that was absent should not be updated.
	 */
	public function has(string $field): bool
	{
		return in_array($field, $this->present_fields, true);
	}
}
