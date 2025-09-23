<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Shop;

use App\Models\Configs;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class ConfigOptionResource extends Data
{
	public string $currency;
	public string $default_price_cents;
	public string $default_license;
	public string $default_size;

	public function __construct()
	{
		$this->currency = Configs::getValueAsString('webshop_currency');
		$this->default_price_cents = Configs::getValueAsString('webshop_default_price_cents');
		$this->default_license = Configs::getValueAsString('webshop_default_license');
		$this->default_size = Configs::getValueAsString('webshop_default_size');
	}
}