<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Embed;

use App\Models\Album;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Resource for embedding album data on external websites.
 *
 * Provides minimal album information and photo collection optimized for
 * external embedding. Includes only publicly visible data.
 */
#[TypeScript()]
class EmbedAlbumResource extends Data
{
	/** @var array<string, mixed> */
	public array $album;
	/** @var Collection<int, EmbedPhotoResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Embed.EmbedPhotoResource[]')]
	public Collection $photos;

	public function __construct(Album $album)
	{
		$this->album = [
			'id' => $album->id,
			'title' => $album->title,
			'description' => $album->description,
			'photo_count' => $album->photos->count(),
			'copyright' => $album->copyright,
			'license' => $album->license?->localization(),
		];

		$this->photos = $album->photos->map(fn ($photo) => EmbedPhotoResource::fromModel($photo));
	}

	/**
	 * Create resource from Album model.
	 *
	 * @param Album $album The album model
	 *
	 * @return self
	 */
	public static function fromModel(Album $album): self
	{
		return new self($album);
	}
}
