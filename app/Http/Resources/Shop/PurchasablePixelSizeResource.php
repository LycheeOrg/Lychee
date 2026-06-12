<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Shop;

use App\Enum\PurchasableLicenseType;
use App\Models\PurchasablePixelSize;
use App\Services\MoneyService;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * API resource for a per-purchasable pixel size assignment including price.
 */
#[TypeScript()]
class PurchasablePixelSizeResource extends Data
{
	public function __construct(
		public int $id,
		public int $pixel_size_id,
		public string $label,
		public int $width,
		public int $height,
		public string $price,
		public int $price_cents,
		public PurchasableLicenseType $license_type,
	) {
	}

	/**
	 * @return PurchasablePixelSizeResource
	 */
	public static function fromModel(PurchasablePixelSize $purchasable_pixel_size): self
	{
		$money_service = resolve(MoneyService::class);

		return new self(
			id: $purchasable_pixel_size->id,
			pixel_size_id: $purchasable_pixel_size->pixel_size_id,
			label: $purchasable_pixel_size->pixelSize->label,
			width: $purchasable_pixel_size->pixelSize->width,
			height: $purchasable_pixel_size->pixelSize->height,
			price: $money_service->format($purchasable_pixel_size->price_cents),
			price_cents: intval($purchasable_pixel_size->price_cents->getAmount()),
			license_type: $purchasable_pixel_size->license_type,
		);
	}
}
