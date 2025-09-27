<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Contracts\Models\AbstractAlbum;
use App\DTO\TopAlbumDTO;
use App\Enum\ColumnSortingType;
use App\Enum\TimelineAlbumGranularity;
use App\Enum\TimelinePhotoGranularity;
use App\Http\Resources\GalleryConfigs\RootConfig;
use App\Http\Resources\Models\Utils\AlbumProtectionPolicy;
use App\Http\Resources\Models\Utils\PreFormattedAlbumData;
use App\Http\Resources\Models\Utils\TimelineData;
use App\Http\Resources\Rights\AlbumRightsResource;
use App\Http\Resources\Rights\RootAlbumRightsResource;
use App\Http\Resources\Traits\HasHeaderUrl;
use App\Http\Resources\Traits\HasPrepPhotoCollection;
use App\Models\Configs;
use App\SmartAlbums\BaseSmartAlbum;
use App\SmartAlbums\UntaggedAlbum;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class UnTaggedSmartAlbumResource extends Data implements Arrayable
{
	/** @var Collection<int,ThumbAlbumResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Models.ThumbAlbumResource[]')]
	public Collection $smart_albums;
	/** @var Collection<int,ThumbAlbumResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Models.ThumbAlbumResource[]')]
	public Collection $tag_albums;
	/** @var Collection<int,ThumbAlbumResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Models.ThumbAlbumResource[]')]
	public Collection $pinned_albums;
	/** @var Collection<int,ThumbAlbumResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Models.ThumbAlbumResource[]')]
	public Collection $albums;
	/** @var Collection<int,ThumbAlbumResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Models.ThumbAlbumResource[]')]
	public Collection $shared_albums;

	use HasPrepPhotoCollection;
	use HasHeaderUrl;

	public string $id;
	public string $title;
	/** @var ?Collection<int,PhotoResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Models.PhotoResource[]')]
	public ?Collection $photos;

	public RootConfig $config;
	public RootAlbumRightsResource $rights;

	public int $current_page;
	public int $from;
	public int $last_page;
	public int $per_page;
	public int $to;
	public int $total;

	public ?ThumbResource $thumb;
	public AlbumProtectionPolicy $policy;
	public PreFormattedAlbumData $preFormattedData;
	public null $statistics = null; // Needed to unify the API response with the AlbumResource and TagAlbumResource.

	public function __construct(
		Collection $smart_albums,
		Collection $tag_albums,
		Collection $pinned_albums,
		Collection $albums,
		Collection $shared_albums,
		RootConfig $config,
		RootAlbumRightsResource $rights,
		LengthAwarePaginator $photos,
	) {
		$this->smart_albums = $smart_albums;
		$this->tag_albums = $tag_albums;
		$this->albums = $albums;
		$this->pinned_albums = $pinned_albums;
		$sorting = Configs::getValueAsEnum('sorting_albums_col', ColumnSortingType::class);
		$album_granularity = Configs::getValueAsEnum('timeline_albums_granularity', TimelineAlbumGranularity::class);
		$this->albums = TimelineData::setTimeLineDataForAlbums($this->albums, $sorting, $album_granularity);
		$this->shared_albums = $shared_albums;
		$this->config = $config;
		$this->rights = $rights;

		$smart_album = UntaggedAlbum::getInstance();
		$this->id = $smart_album->get_id();
		$this->title = $smart_album->get_title();

		if ($photos !== null) {
			$this->photos = collect($photos->items());
			$this->current_page = $photos->currentPage();
			$this->from = $photos->firstItem() ?? 0;
			$this->last_page = $photos->lastPage();
			$this->per_page = $photos->perPage();
			$this->to = $photos->lastItem() ?? 0;
			$this->total = $photos->total();

			$this->prepPhotosCollection();
			// Prep collection with first and last link + which id is next.
			$this->prepPhotosCollection();

			// setup timeline data
			$photo_granularity = Configs::getValueAsEnum('timeline_photos_granularity', TimelinePhotoGranularity::class);
			$this->photos = TimelineData::setTimeLineDataForPhotos($this->photos, $photo_granularity);
		}

		$this->thumb = ThumbResource::fromModel($smart_album->get_thumb());
		$this->policy = AlbumProtectionPolicy::ofSmartAlbum($smart_album);
		$url = $this->getHeaderUrl($smart_album);
		$this->preFormattedData = new PreFormattedAlbumData($smart_album, $url);
	}

    /**
     * @param LengthAwarePaginator<int,\App\Models\Photo> $photos
     *
     * @return self
     */
	public static function fromData(TopAlbumDTO $dto, RootConfig $config, LengthAwarePaginator $photos): self
	{
		$smart_album = UntaggedAlbum::getInstance();

        /** @disregard Undefined method through() (stupid intelephense) */ return new self(
            /** @phpstan-ignore method.notFound (this methods exists, it's in the doc...) */
			smart_albums: ThumbAlbumResource::collect($dto->smart_albums->values()),
            tag_albums: ThumbAlbumResource::collect($dto->tag_albums),
            pinned_albums: ThumbAlbumResource::collect($dto->pinned_albums),
            albums: ThumbAlbumResource::collect($dto->albums),
            shared_albums: $dto->shared_albums !== null ? ThumbAlbumResource::collect($dto->shared_albums) : collect([]),
            config: $config,
            rights: new RootAlbumRightsResource(),
            /** @phpstan-ignore method.notFound */
			photos: $photos->through(fn ($p) => new PhotoResource($p, $smart_album)),
        );
	}

	public function toResponseFormat($request): array
	{
		return [
			'config' => $this->config->toArray(),
			'resource' => [
				'id' => $this->id,
				'title' => $this->title,
				'photos' => $this->photos->toArray(),
				'thumb' => $this->thumb?->toArray(),
				'policy' => $this->policy->toArray(),
				'preFormattedData' => $this->preFormattedData->toArray(),
				'rights' => $this->rights->toArray(),
				'statistics' => $this->statistics,
			],
			'photos' => $this->photos->toArray(),
			'from' => $this->from,
			'per_page' => $this->per_page,
			'total' => $this->total,
		];
	}
}
