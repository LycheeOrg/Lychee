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
use App\Models\TagAlbum;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
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

	public function __construct(TagAlbum $tagAlbum)
	{
		// basic
		$this->id = $tagAlbum->id;
		$this->title = $tagAlbum->title;
		$this->owner_name = Auth::check() ? $tagAlbum->owner->name : null;
		$this->is_tag_album = true;
		$this->show_tags = $tagAlbum->show_tags;
		$this->copyright = $tagAlbum->copyright;

		// children
		$this->photos = $tagAlbum->relationLoaded('photos') ? PhotoResource::collect($tagAlbum->photos) : null;
		if ($this->photos !== null) {
			// Prep collection with first and last link + which id is next.
			$this->prepPhotosCollection();

			// setup timeline data
			$photo_granularity = $this->getPhotoTimeline($tagAlbum->photo_timeline);
			$this->photos = TimelineData::setTimeLineDataForPhotos($this->photos, $photo_granularity);
		}

		// thumb
		$this->thumb = ThumbResource::fromModel($tagAlbum->thumb);

		// security
		$this->policy = AlbumProtectionPolicy::ofBaseAlbum($tagAlbum);
		$this->rights = new AlbumRightsResource($tagAlbum);
		$url = $this->getHeaderUrl($tagAlbum);
		$this->preFormattedData = new PreFormattedAlbumData($tagAlbum, $url);

		if ($this->rights->can_edit) {
			$this->editable = EditableBaseAlbumResource::fromModel($tagAlbum);
		}
	}

	public static function fromModel(TagAlbum $tagAlbum): TagAlbumResource
	{
		return new self($tagAlbum);
	}
}
