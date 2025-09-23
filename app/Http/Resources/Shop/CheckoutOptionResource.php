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
class CheckoutOptionResource extends Data
{
	public string $currency;
	public bool $allow_guest_checkout;
	public string $terms_url;
	public string $privacy_url;

	public function __construct()
	{
		$this->currency = Configs::getValueAsString('webshop_currency');
		$this->allow_guest_checkout = Configs::getValueAsBool('webshop_allow_guest_checkout');
		$this->terms_url = Configs::getValueAsString('webshop_terms_url');
		$this->privacy_url = Configs::getValueAsString('webshop_privacy_url');
	}
}