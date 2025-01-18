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
use App\Legacy\V1\Resources\Traits\WithStatus;
use App\Models\TagAlbum;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

/**
 * Format a Tag album with all the required data.
 */
final class TagAlbumResource extends JsonResource
{
	use WithStatus;

	public function __construct(TagAlbum $tagAlbum)
	{
		parent::__construct($tagAlbum);
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
			'is_tag_album' => true,

			// attributes
			'description' => $this->resource->description,
			'show_tags' => $this->resource->show_tags,

			// children
			'photos' => PhotoCollectionResource::make($this->whenLoaded('photos')),

			// thumb
			'thumb' => $this->resource->thumb,

			// timestamps
			'created_at' => $this->resource->created_at->toIso8601String(),
			'updated_at' => $this->resource->updated_at->toIso8601String(),
			'max_taken_at' => $this->resource->min_taken_at?->toIso8601String(),
			'min_taken_at' => $this->resource->max_taken_at?->toIso8601String(),

			// security
			'policy' => AlbumProtectionPolicy::ofBaseAlbum($this->resource),
			'rights' => AlbumRightsResource::make($this->resource)->toArray($request),
		];
	}
}
