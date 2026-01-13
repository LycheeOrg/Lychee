<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Collections;

use App\Http\Resources\Models\ThumbAlbumResource;
use App\Http\Resources\Models\Utils\TimelineData;
use App\Http\Resources\Traits\HasTimelineData;
use App\Models\Album;
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
	 */
	public function __construct(LengthAwarePaginator $albums, Album $album)
	{
		$this->data = ThumbAlbumResource::collect(collect($albums->items()));
		$this->current_page = $albums->currentPage();
		$this->last_page = $albums->lastPage();
		$this->per_page = $albums->perPage();
		$this->total = $albums->total();

		// setup timeline data
		$sorting = $album->getEffectiveAlbumSorting()->column;
		$album_granularity = $this->getAlbumTimeline($album->album_timeline);

		$this->data = TimelineData::setTimeLineDataForAlbums($this->data, $sorting, $album_granularity);
	}
}
