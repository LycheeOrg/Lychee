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
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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

	public int $current_page;
	public int $from;
	public int $last_page;
	public int $per_page;
	public int $to;
	public int $total;

	public function __construct(BaseSmartAlbum $smart_album, $paginated_photos = null)
	{
		$this->id = $smart_album->get_id();
		$this->title = $smart_album->get_title();

		if ($paginated_photos instanceof LengthAwarePaginator) {
			// Use provided paginated photos
            $this->photos = collect($paginated_photos->items())->map(fn ($photo) => new PhotoResource($photo, $smart_album));
			$this->current_page = $paginated_photos->currentPage();
			$this->from = $paginated_photos->firstItem() ?? 0;
			$this->last_page = $paginated_photos->lastPage();
			$this->per_page = $paginated_photos->perPage();
			$this->to = $paginated_photos->lastItem() ?? 0;
			$this->total = $paginated_photos->total();
		} else {
			// Fallback to non-paginated behavior
			$this->photos = $smart_album->relationLoaded('photos') ? $this->toPhotoResources($smart_album->getPhotos(), $smart_album) : null;
		}

		$this->prepPhotosCollection();
		if ($this->photos !== null) {
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
