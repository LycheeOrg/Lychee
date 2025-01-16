<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Collections;

use App\Http\Resources\Models\PhotoResource;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Resource returned when querying for pictures on the map.
 */
#[TypeScript()]
class PositionDataResource extends Data
{
	public ?string $id;
	public ?string $title;
	public ?string $track_url;
	/** @var ?Collection<int,PhotoResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Models.PhotoResource[]')]
	public ?Collection $photos;

	/**
	 * @param string|null                       $id        the ID of the album; `null` for root album
	 * @param string|null                       $title     the title of the album; `null` if untitled
	 * @param Collection<int,\App\Models\Photo> $photos    the collection of photos with position data to be shown on map
	 * @param string|null                       $track_url the URL of the album's track
	 */
	public function __construct(
		?string $id,
		?string $title,
		Collection $photos,
		?string $track_url,
	) {
		$this->id = $id;
		$this->title = $title;
		$this->track_url = $track_url;
		$this->photos = PhotoResource::collect($photos);
	}
}
