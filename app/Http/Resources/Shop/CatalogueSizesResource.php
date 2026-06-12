<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Shop;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Response containing active print and pixel sizes for a purchasable, including per-size prices.
 */
#[TypeScript()]
class CatalogueSizesResource extends Data
{
	/**
	 * @param PurchasablePrintSizeResource[] $print_sizes
	 * @param PurchasablePixelSizeResource[] $pixel_sizes
	 */
	public function __construct(
		/** @var PurchasablePrintSizeResource[] */
		public array $print_sizes,
		/** @var PurchasablePixelSizeResource[] */
		public array $pixel_sizes,
	) {
	}
}
