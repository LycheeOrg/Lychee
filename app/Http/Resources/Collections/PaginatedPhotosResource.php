<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Collections;

use App\Enum\TimelinePhotoGranularity;
use App\Http\Resources\Models\PhotoResource;
use App\Http\Resources\Models\Utils\TimelineData;
use App\Http\Resources\Traits\HasPrepPhotoCollection;
use App\Http\Resources\Traits\HasTimelineData;
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
	 * @param string|null                              $album_id          the album ID for photo resources
	 * @param TimelinePhotoGranularity|null            $photo_timeline    the timeline granularity setting
	 */
	public function __construct(?LengthAwarePaginator $paginated_photos, ?string $album_id, ?TimelinePhotoGranularity $photo_timeline = null)
	{
		$this->photos = $this->toPhotoResources(collect($paginated_photos?->items() ?? []), $album_id);
		$this->current_page = $paginated_photos?->currentPage() ?? 1;
		$this->last_page = $paginated_photos?->lastPage() ?? 1;
		$this->per_page = $paginated_photos?->perPage() ?? 0;
		$this->total = $paginated_photos?->total() ?? 0;

		$this->prepPhotosCollection();

		if ($photo_timeline === null) {
			return;
		}

		// setup timeline data
		$photo_granularity = $this->getPhotoTimeline($photo_timeline);
		$this->photos = TimelineData::setTimeLineDataForPhotos($this->photos, $photo_granularity);
	}
}
