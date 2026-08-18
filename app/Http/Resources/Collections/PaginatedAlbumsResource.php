<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Collections;

use App\DTO\AlbumSortingCriterion;
use App\Enum\TimelineAlbumGranularity;
use App\Http\Resources\Models\ThumbAlbumResource;
use App\Http\Resources\Models\Utils\TimelineData;
use App\Http\Resources\Traits\HasTimelineData;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class PaginatedAlbumsResource extends Data
{
	use HasTimelineData;

	/** @var Collection<int,ThumbAlbumResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Models.ThumbAlbumResource[]')]
	public Collection $data;

	public int $current_page;
	public int $last_page;
	public int $per_page;
	public int $total;

	/**
	 * @param LengthAwarePaginator<\App\Models\Album> $albums
	 * @param AlbumSortingCriterion                   $sorting        the sorting criterion the albums were fetched with
	 * @param TimelineAlbumGranularity|null           $album_timeline the owning album/criterion's timeline granularity setting, if any (`null` for TagAlbum/PersonAlbum, which have no per-album timeline setting)
	 */
	public function __construct(LengthAwarePaginator $albums, AlbumSortingCriterion $sorting, ?TimelineAlbumGranularity $album_timeline)
	{
		$this->data = ThumbAlbumResource::collect(collect($albums->items()));
		$this->current_page = $albums->currentPage();
		$this->last_page = $albums->lastPage();
		$this->per_page = $albums->perPage();
		$this->total = $albums->total();

		// setup timeline data
		$album_granularity = $this->getAlbumTimeline($album_timeline);

		$this->data = TimelineData::setTimeLineDataForAlbums($this->data, $sorting->column, $album_granularity);
	}
}
