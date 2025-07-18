<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Collections;

use App\DTO\TopAlbumDTO;
use App\Enum\ColumnSortingType;
use App\Enum\TimelineAlbumGranularity;
use App\Http\Resources\GalleryConfigs\RootConfig;
use App\Http\Resources\Models\ThumbAlbumResource;
use App\Http\Resources\Models\Utils\TimelineData;
use App\Http\Resources\Rights\RootAlbumRightsResource;
use App\Models\Configs;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Data Transfer Object (DTO) to transmit the top albums to the client.
 *
 * This DTO differentiates between albums which are owned by the user and
 * "shared" albums which the user does not own, but is allowed to see.
 * The term "shared album" might be a little misleading here.
 * Albums which are owned by the user himself may also be shared (with
 * other users.)
 * Actually, in this context "shared albums" means "foreign albums".
 */
#[TypeScript()]
class RootAlbumResource extends Data
{
	/** @var Collection<int,ThumbAlbumResource> */
	public Collection $smart_albums;
	/** @var Collection<int,ThumbAlbumResource> */
	public Collection $tag_albums;
	/** @var Collection<int,ThumbAlbumResource> */
	public Collection $pinned_albums;
	/** @var Collection<int,ThumbAlbumResource> */
	public Collection $unpinned_albums;
	/** @var Collection<int,ThumbAlbumResource> */
	public Collection $shared_albums;
	public RootConfig $config;
	public RootAlbumRightsResource $rights;

	/**
	 * @param Collection<int,ThumbAlbumResource> $smart_albums
	 * @param Collection<int,ThumbAlbumResource> $tag_albums
	 * @param Collection<int,ThumbAlbumResource> $pinned_albums
	 * @param Collection<int,ThumbAlbumResource> $unpinned_albums
	 * @param Collection<int,ThumbAlbumResource> $shared_albums
	 * @param RootConfig                         $config
	 *
	 * @return void
	 */
	public function __construct(
		Collection              $smart_albums,
		Collection              $tag_albums,
		Collection              $pinned_albums,
		Collection              $unpinned_albums,
		Collection              $shared_albums,
		RootConfig              $config,
		RootAlbumRightsResource $rights,
	) {
		$this->smart_albums = $smart_albums;
		$this->tag_albums = $tag_albums;
		$this->pinned_albums = $pinned_albums;
		$this->unpinned_albums = $unpinned_albums;
		$sorting = Configs::getValueAsEnum('sorting_albums_col', ColumnSortingType::class);
		$album_granularity = Configs::getValueAsEnum('timeline_albums_granularity', TimelineAlbumGranularity::class);
		$this->unpinned_albums = TimelineData::setTimeLineDataForAlbums($this->unpinned_albums, $sorting, $album_granularity);
		$this->shared_albums = $shared_albums;
		$this->config = $config;
		$this->rights = $rights;
	}

	public static function fromDTO(TopAlbumDTO $dto, RootConfig $config): self
	{
		return new self(
			smart_albums: ThumbAlbumResource::collect($dto->smart_albums->values()),
			tag_albums: ThumbAlbumResource::collect($dto->tag_albums),
			pinned_albums: ThumbAlbumResource::collect($dto->pinned_albums),
			unpinned_albums: ThumbAlbumResource::collect($dto->unpinned_albums),
			shared_albums: $dto->shared_albums !== null ? ThumbAlbumResource::collect($dto->shared_albums) : collect([]),
			config: $config,
			rights: new RootAlbumRightsResource()
		);
	}
}