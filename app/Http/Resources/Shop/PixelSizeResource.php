<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Shop;

use App\Models\PixelSize;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * API resource for a global pixel size catalogue entry.
 * Price is not included here; per-purchasable prices are in PurchasablePixelSizeResource.
 */
#[TypeScript()]
class PixelSizeResource extends Data
{
	public function __construct(
		public int $id,
		public string $label,
		public int $width,
		public int $height,
		public bool $is_active,
	) {
	}

	/**
	 * @return PixelSizeResource
	 */
	public static function fromModel(PixelSize $pixel_size): self
	{
		return new self(
			id: $pixel_size->id,
			label: $pixel_size->label,
			width: $pixel_size->width,
			height: $pixel_size->height,
			is_active: $pixel_size->is_active,
		);
	}
}
