<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Enum\SizeVariantType;
use App\Models\Photo;
use App\Policies\PhotoPolicy;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class SizeVariantsResouce extends Data
{
	public ?SizeVariantResource $original;
	public ?SizeVariantResource $medium2x;
	public ?SizeVariantResource $medium;
	public ?SizeVariantResource $small2x;
	public ?SizeVariantResource $small;
	public ?SizeVariantResource $thumb2x;
	public ?SizeVariantResource $thumb;
	public ?SizeVariantResource $placeholder;

	public function __construct(Photo $photo)
	{
		$size_variants = $photo->relationLoaded('size_variants') ? $photo->size_variants : null;
		$downgrade = !Gate::check(PhotoPolicy::CAN_ACCESS_FULL_PHOTO, [Photo::class, $photo]) &&
			!$photo->isVideo() &&
			$size_variants?->hasMedium() === true;

		$original = $size_variants?->getSizeVariant(SizeVariantType::ORIGINAL);
		$medium = $size_variants?->getSizeVariant(SizeVariantType::MEDIUM);
		$medium2x = $size_variants?->getSizeVariant(SizeVariantType::MEDIUM2X);
		$small = $size_variants?->getSizeVariant(SizeVariantType::SMALL);
		$small2x = $size_variants?->getSizeVariant(SizeVariantType::SMALL2X);
		$thumb = $size_variants?->getSizeVariant(SizeVariantType::THUMB);
		$thumb2x = $size_variants?->getSizeVariant(SizeVariantType::THUMB2X);
		$placeholder = $size_variants?->getSizeVariant(SizeVariantType::PLACEHOLDER);

		$this->medium = $medium?->toResource();
		$this->medium2x = $medium2x?->toResource();
		$this->original = $original?->toResource($downgrade);
		$this->small = $small?->toResource();
		$this->small2x = $small2x?->toResource();
		$this->thumb = $thumb?->toResource();
		$this->thumb2x = $thumb2x?->toResource();
		$this->placeholder = $placeholder?->toResource();
	}
}
