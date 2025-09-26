<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Http\Resources\Editable\EditableBaseAlbumResource;
use App\Http\Resources\Models\Utils\AlbumProtectionPolicy;
use App\Http\Resources\Models\Utils\PreFormattedAlbumData;
use App\Http\Resources\Models\Utils\TimelineData;
use App\Http\Resources\Rights\AlbumRightsResource;
use App\Http\Resources\Traits\HasHeaderUrl;
use App\Http\Resources\Traits\HasPrepPhotoCollection;
use App\Http\Resources\Traits\HasTimelineData;
use App\Models\Configs;
use App\Models\TagAlbum;
use App\Models\UnTaggedAlbum;
use App\Policies\AlbumPolicy;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class UnTaggedAlbumResource extends Data
{
	use HasPrepPhotoCollection;
	use HasHeaderUrl;
	use HasTimelineData;

	public string $id;
	public string $title;
	public ?string $owner_name;
	public ?string $copyright;
	public bool $is_untagged_album;

	/** @var ?Collection<int,PhotoResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Models.PhotoResource[]')]
	public ?Collection $photos;

	public int $current_page;
	public int $from;
	public int $last_page;
	public int $per_page;
	public int $to;
	public int $total;

	// thumb
	public ThumbResource|null $thumb;

	// security
	public AlbumProtectionPolicy $policy;
	public AlbumRightsResource $rights;
	public PreFormattedAlbumData $preFormattedData;
	public ?EditableBaseAlbumResource $editable;

	public ?AlbumStatisticsResource $statistics = null;

	public function __construct(
		UnTaggedAlbum $untagged_album,
		LengthAwarePaginator $photos,
	) {
		// basic
		$this->id = $untagged_album->id;
		$this->title = $untagged_album->title;
		$this->owner_name = Auth::check() ? $untagged_album->owner->name : null;
		$this->is_untagged_album = true;
		$this->copyright = $untagged_album->copyright;

		// children
		$this->photos = $untagged_album->relationLoaded('photos') ? $this->toPhotoResources($untagged_album->photos, $untagged_album) : null;
		if ($this->photos !== null) {
			// Prep collection with first and last link + which id is next.
			$this->prepPhotosCollection();

			// setup timeline data
			$photo_granularity = $this->getPhotoTimeline($untagged_album->photo_timeline);
			$this->photos = TimelineData::setTimeLineDataForPhotos($this->photos, $photo_granularity);
		}

		// thumb
		$this->thumb = ThumbResource::fromModel($untagged_album->thumb);

		// security
		$this->policy = AlbumProtectionPolicy::ofBaseAlbum($untagged_album);
		$this->rights = new AlbumRightsResource($untagged_album);
		$url = $this->getHeaderUrl($untagged_album);
		$this->preFormattedData = new PreFormattedAlbumData($untagged_album, $url);

		if ($this->rights->can_edit) {
			$this->editable = EditableBaseAlbumResource::fromModel($untagged_album);
		}

		if (Configs::getValueAsBool('metrics_enabled') && Gate::check(AlbumPolicy::CAN_READ_METRICS, [TagAlbum::class, $untagged_album])) {
			$this->statistics = AlbumStatisticsResource::fromModel($untagged_album->statistics);
		}

		$this->current_page = $photos->currentPage();
		$this->from = $photos->firstItem() ?? 0;
		$this->last_page = $photos->lastPage();
		$this->per_page = $photos->perPage();
		$this->to = $photos->lastItem() ?? 0;
		$this->total = $photos->total();

		$this->prepPhotosCollection();
	}

	public static function fromModel(LengthAwarePaginator $photos, UnTaggedAlbum $untagged_album): self
	{
		/** @disregard Undefined method through() (stupid intelephense) */ return new self(
			untagged_album: ThumbAlbumResource::collect($untagged_album),
			/** @phpstan-ignore method.notFound (this methods exists, it's in the doc...) */
			photos: $photos->through(fn ($p) => new PhotoResource($p, $untagged_album)),
		);
	}
}
