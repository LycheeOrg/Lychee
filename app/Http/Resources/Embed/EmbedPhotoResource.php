<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Embed;

use App\Http\Resources\Models\SizeVariantsResouce;
use App\Models\Photo;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Resource for embedding photo data on external websites.
 *
 * Provides photo information including all size variants and EXIF data
 * needed for displaying photos in embedded galleries.
 */
#[TypeScript()]
class EmbedPhotoResource extends Data
{
	public string $id;
	public ?string $title;
	public ?string $description;
	public SizeVariantsResouce $size_variants;
	/** @var array<string, string|null> */
	public array $exif;

	public function __construct(Photo $photo)
	{
		$this->id = $photo->id;
		$this->title = $photo->title;
		$this->description = $photo->description;

		// Reuse existing SizeVariantsResouce instead of duplicating logic
		// Pass null for album since embeds are always public
		$this->size_variants = new SizeVariantsResouce($photo, null);

		// Simplified EXIF data for embeds
		$this->exif = [
			'make' => $photo->make,
			'model' => $photo->model,
			'lens' => $photo->lens,
			'iso' => $photo->iso,
			'aperture' => $photo->aperture,
			'shutter' => $photo->shutter,
			'focal' => $photo->focal,
			'taken_at' => $photo->taken_at?->toIso8601String(),
		];
	}

	/**
	 * Create resource from Photo model.
	 *
	 * @param Photo $photo The photo model
	 *
	 * @return self
	 */
	public static function fromModel(Photo $photo): self
	{
		return new self($photo);
	}
}
