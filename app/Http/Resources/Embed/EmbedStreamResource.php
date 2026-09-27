<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
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

		// Feature 070 (FR-070-06): per photo, one grouped query. Previously a
		// single boolean off the `grants_full_photo_access` config, which only
		// seeds newly created shares - reading it as a gate over-granted.
		/** @var \App\Models\User|null $user */
		$user = \Illuminate\Support\Facades\Auth::user();
		$should_downgrade = resolve(\App\Actions\Photo\StructOfArrays\ResolvesPhotoGrants::class)
			->downgradeMap($photos, $user);

		$this->photos = $photos->map(fn ($photo) => EmbedPhotoResource::fromModel(
			photo: $photo,
			should_downgrade: $should_downgrade[$photo->id] ?? true,
		));
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
