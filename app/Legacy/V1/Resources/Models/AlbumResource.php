<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Resources\Models;

use App\Http\Resources\Models\Utils\AlbumProtectionPolicy;
use App\Legacy\V1\Resources\Collections\AlbumCollectionResource;
use App\Legacy\V1\Resources\Collections\PhotoCollectionResource;
use App\Legacy\V1\Resources\Rights\AlbumRightsResource;
use App\Legacy\V1\Resources\Traits\WithStatus;
use App\Models\Album;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

/**
 * Basic album conversion when using get().
 */
final class AlbumResource extends JsonResource
{
	use WithStatus;

	public function __construct(Album $album)
	{
		parent::__construct($album);
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
			'owner_name' => $this->when(Auth::check(), $this->resource->owner->name),
			'copyright' => $this->resource->copyright,

			// attributes
			'description' => $this->resource->description,
			'track_url' => $this->resource->track_url,
			'license' => $this->resource->license->localization(),
			'sorting' => $this->resource->photo_sorting,
			'header_id' => $this->resource->header_id,

			// children
			'parent_id' => $this->resource->parent_id,
			'has_albums' => !$this->resource->isLeaf(),
			'albums' => AlbumCollectionResource::make($this->whenLoaded('children')),
			'photos' => PhotoCollectionResource::make($this->whenLoaded('photos')),
			'num_subalbums' => $this->resource->num_children,
			'num_photos' => $this->resource->num_photos,

			// thumb
			'cover_id' => $this->resource->cover_id,
			'thumb' => $this->resource->thumb,

			// timestamps
			'created_at' => $this->resource->created_at->toIso8601String(),
			'updated_at' => $this->resource->updated_at->toIso8601String(),
			'max_taken_at' => $this->resource->max_taken_at?->toIso8601String(),
			'min_taken_at' => $this->resource->min_taken_at?->toIso8601String(),

			// security
			'policy' => AlbumProtectionPolicy::ofBaseAlbum($this->resource),
			'rights' => AlbumRightsResource::make($this->resource)->toArray($request),
		];
	}
}
