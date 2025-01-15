<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Enum\ColumnSortingType;
use App\Http\Resources\Editable\EditableBaseAlbumResource;
use App\Http\Resources\Models\Utils\AlbumProtectionPolicy;
use App\Http\Resources\Models\Utils\PreFormattedAlbumData;
use App\Http\Resources\Models\Utils\TimelineData;
use App\Http\Resources\Rights\AlbumRightsResource;
use App\Http\Resources\Traits\HasHeaderUrl;
use App\Http\Resources\Traits\HasPrepPhotoCollection;
use App\Http\Resources\Traits\HasTimelineData;
use App\Models\Album;
use App\Models\Configs;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class AlbumResource extends Data
{
	use HasPrepPhotoCollection;
	use HasHeaderUrl;
	use HasTimelineData;

	public string $id;
	public string $title;
	public ?string $owner_name;
	public ?string $description;
	public ?string $copyright;

	// attributes
	public ?string $track_url;
	public string $license;
	public ?string $header_id;

	// children
	public ?string $parent_id;
	public bool $has_albums;
	/** @var ?Collection<int,ThumbAlbumResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Models.ThumbAlbumResource[]')]
	public ?Collection $albums;
	/** @var ?Collection<int,PhotoResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Models.PhotoResource[]')]
	public ?Collection $photos;

	// thumb
	public ?string $cover_id;
	public ?ThumbResource $thumb;

	// security
	public AlbumProtectionPolicy $policy;
	public AlbumRightsResource $rights;
	public PreFormattedAlbumData $preFormattedData;
	public ?EditableBaseAlbumResource $editable;

	public function __construct(Album $album)
	{
		$this->id = $album->id;
		$this->title = $album->title;
		$this->description = $album->description;
		$this->owner_name = Auth::check() ? $album->owner->name : null;
		$this->copyright = $album->copyright;

		// attributes
		$this->track_url = $album->track_url;
		$this->license = $album->license->localization();
		// TODO: Investigate later why this string is 24 characters long.
		$this->header_id = trim($album->header_id);

		// children
		$this->parent_id = $album->parent_id;
		$this->has_albums = !$album->isLeaf();
		$this->albums = $album->relationLoaded('children') ? ThumbAlbumResource::collect($album->children) : null;
		$this->photos = $album->relationLoaded('photos') ? PhotoResource::collect($album->photos) : null;
		if ($this->photos !== null) {
			// Prep collection with first and last link + which id is next.
			$this->prepPhotosCollection();

			// setup timeline data
			$photo_granularity = $this->getPhotoTimeline($album->photo_timeline);
			$this->photos = TimelineData::setTimeLineDataForPhotos($this->photos, $photo_granularity);
		}

		if ($this->albums->count() > 0) {
			// setup timeline data
			$sorting = $album->album_sorting?->column ?? Configs::getValueAsEnum('sorting_albums_col', ColumnSortingType::class);
			$album_granularity = $this->getAlbumTimeline($album->album_timeline);
			$this->albums = TimelineData::setTimeLineDataForAlbums($this->albums, $sorting, $album_granularity);
		}

		// thumb
		$this->cover_id = $album->cover_id;
		$this->thumb = ThumbResource::fromModel($album->thumb);

		// security
		$this->policy = AlbumProtectionPolicy::ofBaseAlbum($album);
		$this->rights = new AlbumRightsResource($album);
		$url = $this->getHeaderUrl($album);
		$this->preFormattedData = new PreFormattedAlbumData($album, $url);

		if ($this->rights->can_edit) {
			$this->editable = EditableBaseAlbumResource::fromModel($album);
		}
	}

	public static function fromModel(Album $album): AlbumResource
	{
		return new self($album);
	}
}
