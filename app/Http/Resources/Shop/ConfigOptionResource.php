<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Shop;

use App\Enum\PurchasableLicenseType;
use App\Enum\PurchasableSizeVariantType;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class ConfigOptionResource extends Data
{
	public string $currency;
	public int $default_price_cents;
	public PurchasableLicenseType $default_license;
	public PurchasableSizeVariantType $default_size;

	public function __construct()
	{
		$this->currency = request()->configs()->getValueAsString('webshop_currency');
		$this->default_price_cents = request()->configs()->getValueAsInt('webshop_default_price_cents');
		$this->default_license = request()->configs()->getValueAsEnum('webshop_default_license', PurchasableLicenseType::class);
		$this->default_size = request()->configs()->getValueAsEnum('webshop_default_size', PurchasableSizeVariantType::class);
	}
}