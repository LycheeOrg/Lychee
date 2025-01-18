<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Resources\Models;

use App\Http\Resources\Models\Utils\AlbumProtectionPolicy;
use App\Legacy\V1\Resources\Collections\PhotoCollectionResource;
use App\Legacy\V1\Resources\Rights\AlbumRightsResource;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Converts a Smart album into a resource with all the required data.
 */
final class SmartAlbumResource extends JsonResource
{
	public function __construct(BaseSmartAlbum $smartAlbum)
	{
		parent::__construct($smartAlbum);
	}

	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return array<string,mixed>|\Illuminate\Contracts\Support\Arrayable<string,mixed>|\JsonSerializable
	 */
	public function toArray($request)
	{
		return [
			// basic
			'id' => $this->resource->id,
			'title' => $this->resource->title,

			// We use getPhotos() to be sure to not execute and cache the photos.
			// Some of the tests do check what is the value of the thumb id as a result,
			// if the id is not in thumb (intended behaviour we want to check)
			// but still in the photos (supposed to be null), this fail the test.
			'photos' => $this->whenLoaded('photos', PhotoCollectionResource::make($this->resource->getPhotos() ?? []), null),

			// thumb
			'thumb' => $this->resource->thumb,

			// security
			'policy' => AlbumProtectionPolicy::ofSmartAlbum($this->resource)->toArray(),
			'rights' => AlbumRightsResource::make($this->resource)->toArray($request),
		];
	}
}
