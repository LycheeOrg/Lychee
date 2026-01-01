<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Shop;

use App\Enum\PurchasableLicenseType;
use App\Enum\PurchasableSizeVariantType;
use App\Models\PurchasablePrice;
use App\Services\MoneyService;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * This containes the Price information for the purchasable item.
 * Both price and price_cents are provided for convenience.
 * price is used for display purposes, while price_cents is used for calculations.
 */
#[TypeScript()]
class PriceResource extends Data
{
	public function __construct(
		public PurchasableSizeVariantType $size_variant,
		public PurchasableLicenseType $license_type,
		public string $price,
		public int $price_cents,
	) {
	}

	/**
	 * @return PriceResource
	 */
	public static function fromModel(PurchasablePrice $price): PriceResource
	{
		$money_service = resolve(MoneyService::class);

		return new self(
			size_variant: $price->size_variant,
			license_type: $price->license_type,
			price: $money_service->format($price->price_cents),
			price_cents: intval($price->price_cents->getAmount()),
		);
	}
}
