<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Embed;

use App\Enum\SizeVariantType;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource for embedding photo data on external websites.
 *
 * Provides photo information including all size variants and EXIF data
 * needed for displaying photos in embedded galleries.
 */
class EmbedPhotoResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param Request $request The incoming request
	 *
	 * @return array<string, mixed> The photo data formatted for embedding
	 */
	public function toArray(Request $request): array
	{
		/** @var Photo $photo */
		$photo = $this->resource;

		$sizeVariants = $photo->relationLoaded('size_variants') ? $photo->size_variants : null;

		return [
			'id' => $photo->id,
			'title' => $photo->title,
			'description' => $photo->description,
			'size_variants' => [
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
			],
			'exif' => [
				'make' => $photo->make,
				'model' => $photo->model,
				'lens' => $photo->lens,
				'iso' => $photo->iso,
				'aperture' => $photo->aperture,
				'shutter' => $photo->shutter,
				'focal' => $photo->focal,
				'taken_at' => $photo->taken_at?->toIso8601String(),
			],
		];
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
