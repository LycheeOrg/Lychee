<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Editable;

use App\DTO\AlbumSortingCriterion;
use App\DTO\PhotoSortingCriterion;
use App\Enum\AspectRatioType;
use App\Enum\LicenseType;
use App\Enum\PhotoLayoutType;
use App\Enum\TimelineAlbumGranularity;
use App\Enum\TimelinePhotoGranularity;
use App\Models\Album;
use App\Models\TagAlbum;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class EditableBaseAlbumResource extends Data
{
	public string $id;
	public string $title;
	public ?string $description;
	public ?string $copyright;
	public ?LicenseType $license;
	public ?PhotoSortingCriterion $photo_sorting;
	public ?AlbumSortingCriterion $album_sorting;
	public ?AspectRatioType $aspect_ratio;
	public ?PhotoLayoutType $photo_layout;
	public ?string $header_id;
	public ?string $cover_id;
	public ?TimelineAlbumGranularity $album_timeline;
	public ?TimelinePhotoGranularity $photo_timeline;
	/** @var string[] */
	public array $tags;
	public bool $is_model_album;

	public function __construct(Album|TagAlbum $album)
	{
		$this->id = $album->id;
		$this->title = $album->title;
		$this->description = $album->description;
		$this->copyright = $album->copyright;
		$this->photo_sorting = $album->photo_sorting;
		$this->is_model_album = false;
		$this->license = null;
		$this->album_sorting = null;
		$this->header_id = null;
		$this->cover_id = null;
		$this->photo_layout = $album->photo_layout;
		$this->album_timeline = null;
		$this->photo_timeline = $album->photo_timeline;

		if ($album instanceof Album) {
			$this->is_model_album = true;
			$this->license = $album->license;
			$this->album_sorting = $album->album_sorting;
			$this->header_id = $album->header_id;
			$this->cover_id = $album->cover_id;
			$this->aspect_ratio = $album->album_thumb_aspect_ratio;
			$this->album_timeline = $album->album_timeline;
		}

		if ($album instanceof TagAlbum) {
			$this->tags = $album->show_tags;
		}
	}

	public static function fromModel(Album|TagAlbum $album): EditableBaseAlbumResource
	{
		return new self($album);
	}
}
