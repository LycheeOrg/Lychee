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
 * Includes site title and publicly visible photos.
 */
#[TypeScript()]
class EmbedStreamResource extends Data
{
	public string $site_title;

	/** @var Collection<int, EmbedPhotoResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Embed.EmbedPhotoResource[]')]
	public Collection $photos;

	/**
	 * @param string     $site_title The site title to display
	 * @param Collection $photos     Collection of Photo models
	 */
	public function __construct(string $site_title, Collection $photos)
	{
		$this->site_title = $site_title;
		$this->photos = $photos->map(fn ($photo) => EmbedPhotoResource::fromModel($photo));
	}

	/**
	 * Create resource from photo collection.
	 *
	 * @param string     $site_title The site title to display
	 * @param Collection $photos     Collection of Photo models
	 *
	 * @return self
	 */
	public static function fromPhotos(string $site_title, Collection $photos): self
	{
		return new self($site_title, $photos);
	}
}
