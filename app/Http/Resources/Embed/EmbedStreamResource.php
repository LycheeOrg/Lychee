<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Embed;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Resource for embedding public photo stream on external websites.
 *
 * Provides a collection of public photos optimized for external embedding.
 * Includes only publicly visible data without album context.
 */
#[TypeScript()]
class EmbedStreamResource extends Data
{
	/** @var Collection<int, EmbedPhotoResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Embed.EmbedPhotoResource[]')]
	public Collection $photos;

	/**
	 * @param Collection $photos Collection of Photo models
	 */
	public function __construct(Collection $photos)
	{
		$this->photos = $photos->map(fn ($photo) => EmbedPhotoResource::fromModel($photo));
	}

	/**
	 * Create resource from photo collection.
	 *
	 * @param Collection $photos Collection of Photo models
	 *
	 * @return self
	 */
	public static function fromPhotos(Collection $photos): self
	{
		return new self($photos);
	}
}
