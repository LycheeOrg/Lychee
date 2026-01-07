<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Collections;

use App\Contracts\Models\AbstractAlbum;
use App\Http\Resources\Models\PhotoResource;
use App\Http\Resources\Models\Utils\TimelineData;
use App\Http\Resources\Traits\HasPrepPhotoCollection;
use App\Http\Resources\Traits\HasTimelineData;
use App\Models\Album;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class PaginatedPhotosResource extends Data
{
	use HasPrepPhotoCollection;
	use HasTimelineData;

	/** @var Collection<int,PhotoResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Models.PhotoResource[]')]
	public Collection $photos;

	public int $current_page;
	public int $last_page;
	public int $per_page;
	public int $total;

	/**
	 * @param ?LengthAwarePaginator<\App\Models\Photo> $paginated_photos
	 * @param AbstractAlbum                            $album            the album context for photo resources
	 */
	public function __construct(?LengthAwarePaginator $paginated_photos, AbstractAlbum $album)
	{
		$this->photos = $this->toPhotoResources(collect($paginated_photos?->items() ?? []), $album);
		$this->current_page = $paginated_photos?->currentPage() ?? 1;
		$this->last_page = $paginated_photos?->lastPage() ?? 1;
		$this->per_page = $paginated_photos?->perPage() ?? 0;
		$this->total = $paginated_photos?->total() ?? 0;

		$this->prepPhotosCollection();

		if ($album instanceof Album === false) {
			return;
		}

		// setup timeline data
		$photo_granularity = $this->getPhotoTimeline($album->photo_timeline);
		$this->photos = TimelineData::setTimeLineDataForPhotos($this->photos, $photo_granularity);
	}
}
