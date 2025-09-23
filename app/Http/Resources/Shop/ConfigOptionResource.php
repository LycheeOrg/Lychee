<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Shop;

use App\Enum\PurchasableLicenseType;
use App\Enum\PurchasableSizeVariantType;
use App\Models\Configs;
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
		$this->currency = Configs::getValueAsString('webshop_currency');
		$this->default_price_cents = Configs::getValueAsInt('webshop_default_price_cents');
		$this->default_license = Configs::getValueAsEnum('webshop_default_license', PurchasableLicenseType::class);
		$this->default_size = Configs::getValueAsEnum('webshop_default_size', PurchasableSizeVariantType::class);
	}
}