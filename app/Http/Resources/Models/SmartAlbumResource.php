<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Enum\TimelinePhotoGranularity;
use App\Http\Resources\Models\Utils\AlbumProtectionPolicy;
use App\Http\Resources\Models\Utils\PreFormattedAlbumData;
use App\Http\Resources\Models\Utils\TimelineData;
use App\Http\Resources\Rights\AlbumRightsResource;
use App\Http\Resources\Traits\HasHeaderUrl;
use App\Http\Resources\Traits\HasPrepPhotoCollection;
use App\Models\Configs;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class SmartAlbumResource extends Data
{
	use HasPrepPhotoCollection;
	use HasHeaderUrl;

	public string $id;
	public string $title;
	/** @var ?Collection<int,PhotoResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Models.PhotoResource[]')]
	public ?Collection $photos;
	public ?ThumbResource $thumb;
	public AlbumProtectionPolicy $policy;
	public AlbumRightsResource $rights;
	public PreFormattedAlbumData $preFormattedData;

	public function __construct(BaseSmartAlbum $smartAlbum)
	{
		$this->id = $smartAlbum->id;
		$this->title = $smartAlbum->title;
		/** @phpstan-ignore-next-line */
		$this->photos = $smartAlbum->relationLoaded('photos') ? PhotoResource::collect($smartAlbum->getPhotos()) : null;
		$this->prepPhotosCollection();
		if ($this->photos !== null) {
			// Prep collection with first and last link + which id is next.
			$this->prepPhotosCollection();

			// setup timeline data
			$photo_granularity = Configs::getValueAsEnum('timeline_photos_granularity', TimelinePhotoGranularity::class);
			$this->photos = TimelineData::setTimeLineDataForPhotos($this->photos, $photo_granularity);
		}

		$this->thumb = ThumbResource::fromModel($smartAlbum->thumb);
		$this->policy = AlbumProtectionPolicy::ofSmartAlbum($smartAlbum);
		$this->rights = new AlbumRightsResource($smartAlbum);
		$url = $this->getHeaderUrl($smartAlbum);
		$this->preFormattedData = new PreFormattedAlbumData($smartAlbum, $url);
	}

	public static function fromModel(BaseSmartAlbum $smartAlbum): SmartAlbumResource
	{
		return new self($smartAlbum);
	}
}
