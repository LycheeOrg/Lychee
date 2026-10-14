<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Models\Album;
use App\Models\NsfwDetection;
use App\Models\Photo;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * A photo with its NSFW findings (Feature 089, FR-089-09).
 *
 * Expects `size_variants`, `owner` and `albums` to be eager loaded on the photo.
 */
#[TypeScript()]
class NsfwFindingResource extends Data
{
	public string $photo_id;
	public string $title;
	public ?string $thumb_url;
	public string $owner_username;
	public ?string $nsfw_status;
	public bool $is_validated;
	/** @var Collection<int,NsfwDetectionResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Models.NsfwDetectionResource[]')]
	public Collection $detections;
	/** @var Collection<int,PhotoAlbumResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Models.PhotoAlbumResource[]')]
	public Collection $albums;
	public ?string $scanned_at;

	/**
	 * @param Photo                         $photo
	 * @param Collection<int,NsfwDetection> $detections the photo's detections, highest confidence first
	 */
	public function __construct(Photo $photo, Collection $detections)
	{
		$this->photo_id = $photo->id;
		$this->title = $photo->title;
		$this->thumb_url = $photo->size_variants->getThumb()?->url;
		$this->owner_username = $photo->owner?->username ?? '';
		$this->nsfw_status = $photo->nsfw_status?->value;
		$this->is_validated = $photo->is_validated;
		$this->detections = $detections->map(fn (NsfwDetection $detection) => new NsfwDetectionResource($detection))->values();
		$this->albums = $photo->albums
			->sortBy(fn (Album $album) => $album->title, SORT_NATURAL | SORT_FLAG_CASE)
			->map(fn (Album $album) => new PhotoAlbumResource($album))
			->values();
		$this->scanned_at = $detections->max('created_at')?->toIso8601String();
	}
}
