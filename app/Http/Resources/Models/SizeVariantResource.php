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

	public function __construct(SizeVariant $sizeVariant, bool $noUrl = false)
	{
		$this->type = $sizeVariant->type;
		$this->locale = $sizeVariant->type->localization();
		$this->filesize = Helpers::getSymbolByQuantity(floatval($sizeVariant->filesize));
		$this->height = $sizeVariant->height;
		$this->width = $sizeVariant->width;
		$this->url = !$noUrl ? $sizeVariant->url : null;
	}
}
