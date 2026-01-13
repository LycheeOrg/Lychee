<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Timeline;

use App\Enum\TimelinePhotoGranularity;
use App\Http\Resources\Models\PhotoResource;
use App\Http\Resources\Models\Utils\TimelineData;
use App\Http\Resources\Traits\HasPrepPhotoCollection;
use App\Models\Photo;
use App\Repositories\ConfigManager;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Result of a Search query.
 */
#[TypeScript()]
class TimelineResource extends Data
{
	use HasPrepPhotoCollection;

	/** @var Collection<int,PhotoResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Models.PhotoResource[]')]
	public Collection $photos;

	public int $current_page;
	public int $from;
	public int $last_page;
	public int $per_page;
	public int $to;
	public int $total;

	/**
	 * @param LengthAwarePaginator<int,PhotoResource>&Paginator<int,PhotoResource> $photos
	 *
	 * @return void
	 */
	public function __construct(
		LengthAwarePaginator $photos,
	) {
		$this->photos = collect($photos->items());
		$this->current_page = $photos->currentPage();
		$this->from = $photos->firstItem() ?? 0;
		$this->last_page = $photos->lastPage();
		$this->per_page = $photos->perPage();
		$this->to = $photos->lastItem() ?? 0;
		$this->total = $photos->total();

		// We do it manually this time.
		$previous_photo = null;
		$this->photos->each(function (PhotoResource &$photo) use (&$previous_photo): void {
			if ($previous_photo !== null) {
				$previous_photo->next_photo_id = $photo->id;
			}
			$photo->previous_photo_id = $previous_photo?->id;
			$previous_photo = $photo;
		});
		$photo_granularity = request()->configs()->getValueAsEnum('timeline_photos_granularity', TimelinePhotoGranularity::class);
		$this->photos = TimelineData::setTimeLineDataForPhotos($this->photos, $photo_granularity);
	}

	/**
	 * @param LengthAwarePaginator<int,Photo> $photos
	 *
	 * @return TimelineResource
	 */
	public static function fromData(LengthAwarePaginator $photos): self
	{
		$config_manager = resolve(ConfigManager::class);
		$should_downgrade = !$config_manager->getValueAsBool('grants_full_photo_access');

		/** @disregard Undefined method withQueryString() (stupid intelephense) */
		return new self(
			/** @phpstan-ignore method.notFound (this methods exists, it's in the doc...) */
			photos: $photos->through(fn ($p) => new PhotoResource($p, null, $should_downgrade)),
		);
	}
}