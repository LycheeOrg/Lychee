<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Feed;

use App\Http\Resources\Models\AlbumStatisticsResource;
use App\Http\Resources\Models\PhotoResource;
use App\Http\Resources\Models\Utils\PreFormattedAlbumData;
use App\Http\Resources\Traits\HasHeaderUrl;
use App\Http\Resources\Traits\HasPrepPhotoCollection;
use App\Models\Album;
use App\Models\Configs;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Result of a Search query.
 */
#[TypeScript()]
class FeeditemResource extends Data
{
	use HasPrepPhotoCollection;
	use HasHeaderUrl;

	public string $id;
	public ?string $owner_name;
	public bool $is_nsfw;

	/** @var ?Collection<int,PhotoResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Models.PhotoResource[]')]
	public ?Collection $photos;
	public PreFormattedAlbumData $pre_formatted_data;
	public ?AlbumStatisticsResource $statistics = null;

	/**
	 * @return void
	 */
	public function __construct(
		Album $album,
	) {
		$this->id = $album->id;
		$this->owner_name = Auth::check() ? $album->owner->name : null;
		$this->photos = $album->relationLoaded('photos') ? $this->toPhotoResources($album->photos, $album) : null;
		$this->is_nsfw = $album->is_nsfw;

		if ($this->photos !== null) {
			// Prep collection with first and last link + which id is next.
			$this->prepPhotosCollection();
		}

		if (Configs::getValueAsBool('metrics_enabled') && Gate::check(AlbumPolicy::CAN_READ_METRICS, [Album::class, $album])) {
			$this->statistics = AlbumStatisticsResource::fromModel($album->statistics);
		}
		$url = $this->getHeaderUrl($album);
		$this->pre_formatted_data = new PreFormattedAlbumData($album, $url);
	}

	public static function fromModel(Album $album): FeeditemResource
	{
		return new self($album);
	}
}