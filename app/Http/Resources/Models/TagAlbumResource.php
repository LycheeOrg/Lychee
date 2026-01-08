<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
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
use App\Models\TagAlbum;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class TagAlbumResource extends Data
{
	use HasPrepPhotoCollection;
	use HasHeaderUrl;
	use HasTimelineData;

	public string $id;
	public string $title;
	public ?string $owner_name;
	public ?string $copyright;
	public bool $is_tag_album;

	/** @var string[] */
	public array $show_tags;

	/** @var ?Collection<int,PhotoResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Models.PhotoResource[]')]
	public ?Collection $photos;

	// thumb
	public ThumbResource|null $thumb;

	// security
	public AlbumProtectionPolicy $policy;
	public AlbumRightsResource $rights;
	public PreFormattedAlbumData $preFormattedData;
	public ?EditableBaseAlbumResource $editable;

	public ?AlbumStatisticsResource $statistics = null;

	public function __construct(TagAlbum $tag_album)
	{
		// basic
		$this->id = $tag_album->id;
		$this->title = $tag_album->title;
		$this->owner_name = Auth::check() ? $tag_album->owner->name : null;
		$this->is_tag_album = true;
		$this->show_tags = $tag_album->tags->map(fn ($t) => $t->name)->all();
		$this->copyright = $tag_album->copyright;

		// children
		$this->photos = $tag_album->relationLoaded('photos') ? $this->toPhotoResources(
			photos: $tag_album->photos,
			album_id: $tag_album->id,
			should_downgrade: Gate::check(AlbumPolicy::CAN_ACCESS_FULL_PHOTO, [TagAlbum::class, $tag_album]) === false,
		) : null;
		if ($this->photos !== null) {
			// Prep collection with first and last link + which id is next.
			$this->prepPhotosCollection();

			// setup timeline data
			$photo_granularity = $this->getPhotoTimeline($tag_album->photo_timeline);
			$this->photos = TimelineData::setTimeLineDataForPhotos($this->photos, $photo_granularity);
		}

		// thumb
		$this->thumb = ThumbResource::fromModel($tag_album->thumb);

		// security
		$this->policy = AlbumProtectionPolicy::ofBaseAlbum($tag_album);
		$this->rights = new AlbumRightsResource($tag_album);
		$url = $this->getHeaderUrl($tag_album);
		$this->preFormattedData = new PreFormattedAlbumData($tag_album, $url);

		if ($this->rights->can_edit) {
			$this->editable = EditableBaseAlbumResource::fromModel($tag_album);
		}

		if (request()->configs()->getValueAsBool('metrics_enabled') && Gate::check(AlbumPolicy::CAN_READ_METRICS, [TagAlbum::class, $tag_album])) {
			$this->statistics = AlbumStatisticsResource::fromModel($tag_album->statistics);
		}
	}

	public static function fromModel(TagAlbum $tag_album): TagAlbumResource
	{
		return new self($tag_album);
	}
}
