<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Enum\SizeVariantType;
use App\Facades\Helpers;
use App\Models\SizeVariant;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class SizeVariantResource extends Data
{
	public SizeVariantType $type;
	public string $locale;
	public string $filesize;
	public int $height;
	public int $width;
	public ?string $url;

	public function __construct(SizeVariant $size_variant, bool $no_url = false)
	{
		$this->type = $size_variant->type;
		$this->locale = $size_variant->type->localization();
		$this->filesize = Helpers::getSymbolByQuantity(floatval($size_variant->filesize));
		$this->height = $size_variant->height;
		$this->width = $size_variant->width;
		$this->url = !$no_url ? $size_variant->url : null;
	}
}
