<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Embed;

use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource for embedding album data on external websites.
 *
 * Provides minimal album information and photo collection optimized for
 * external embedding. Includes only publicly visible data.
 */
class EmbedAlbumResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param Request $request The incoming request
	 *
	 * @return array<string, mixed> The album data formatted for embedding
	 */
	public function toArray(Request $request): array
	{
		/** @var Album $album */
		$album = $this->resource;

		return [
			'album' => [
				'id' => $album->id,
				'title' => $album->title,
				'description' => $album->description,
				'photo_count' => $album->photos->count(),
				'copyright' => $album->copyright,
				'license' => $album->license?->localization(),
			],
			'photos' => EmbedPhotoResource::collection($album->photos),
		];
	}
}
