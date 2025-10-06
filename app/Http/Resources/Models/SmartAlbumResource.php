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
	public null $statistics = null; // Needed to unify the API response with the AlbumResource and TagAlbumResource.

	public int $current_page = 0;
	public int $last_page = 0;
	public int $per_page = 0;
	public int $total = 0;

	public function __construct(BaseSmartAlbum $smart_album)
	{
		$this->id = $smart_album->get_id();
		$this->title = $smart_album->get_title();

		/** @disregard P1006 */
		$photos = $smart_album->relationLoaded('photos') ? $smart_album->getPhotos() : null;

		if ($photos !== null) {
			$this->photos = $this->toPhotoResources(collect($photos->items()), $smart_album);
			$this->current_page = $photos->currentPage();
			$this->last_page = $photos->lastPage();
			$this->per_page = $photos->perPage();
			$this->total = $photos->total();

			// Prep collection with first and last link + which id is next.
			$this->prepPhotosCollection();

			// setup timeline data
			$photo_granularity = Configs::getValueAsEnum('timeline_photos_granularity', TimelinePhotoGranularity::class);
			$this->photos = TimelineData::setTimeLineDataForPhotos($this->photos, $photo_granularity);
		}

		$this->thumb = ThumbResource::fromModel($smart_album->get_thumb());
		$this->policy = AlbumProtectionPolicy::ofSmartAlbum($smart_album);
		$this->rights = new AlbumRightsResource($smart_album);
		$url = $this->getHeaderUrl($smart_album);
		$this->preFormattedData = new PreFormattedAlbumData($smart_album, $url);
	}

	public static function fromModel(BaseSmartAlbum $smart_album): SmartAlbumResource
	{
		return new self($smart_album);
	}
}
