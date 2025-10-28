<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Embed;

use App\Enum\SizeVariantType;
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
	/** @var array<string, array<string, int|string>|null> */
	public array $size_variants;
	/** @var array<string, string|null> */
	public array $exif;

	public function __construct(Photo $photo)
	{
		$sizeVariants = $photo->relationLoaded('size_variants') ? $photo->size_variants : null;

		$this->id = $photo->id;
		$this->title = $photo->title;
		$this->description = $photo->description;
		$this->size_variants = [
			'placeholder' => $this->getSizeVariantData($sizeVariants?->getSizeVariant(SizeVariantType::PLACEHOLDER)),
			'thumb' => $this->getSizeVariantData($sizeVariants?->getSizeVariant(SizeVariantType::THUMB)),
			'thumb2x' => $this->getSizeVariantData($sizeVariants?->getSizeVariant(SizeVariantType::THUMB2X)),
			'small' => $this->getSizeVariantData($sizeVariants?->getSizeVariant(SizeVariantType::SMALL)),
			'small2x' => $this->getSizeVariantData($sizeVariants?->getSizeVariant(SizeVariantType::SMALL2X)),
			'medium' => $this->getSizeVariantData($sizeVariants?->getSizeVariant(SizeVariantType::MEDIUM)),
			'medium2x' => $this->getSizeVariantData($sizeVariants?->getSizeVariant(SizeVariantType::MEDIUM2X)),
			'original' => [
				'width' => $sizeVariants?->getSizeVariant(SizeVariantType::ORIGINAL)?->width ?? 0,
				'height' => $sizeVariants?->getSizeVariant(SizeVariantType::ORIGINAL)?->height ?? 0,
			],
		];
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

	/**
	 * Get size variant data as an array.
	 *
	 * @param \App\Models\SizeVariant|null $variant The size variant
	 *
	 * @return array<string, mixed>|null The variant data or null if not available
	 */
	private function getSizeVariantData(?\App\Models\SizeVariant $variant): ?array
	{
		if ($variant === null) {
			return null;
		}

		return [
			'url' => $variant->url,
			'width' => $variant->width,
			'height' => $variant->height,
		];
	}
}
