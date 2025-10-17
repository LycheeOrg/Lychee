<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Shop;

use App\Factories\OmnipayFactory;
use App\Models\Configs;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class CheckoutOptionResource extends Data
{
	public string $currency;
	public bool $allow_guest_checkout;
	public bool $is_offline;
	public string $terms_url;
	public string $privacy_url;
	#[LiteralTypeScriptType('App.Enum.OmnipayProviderType[]')]
	public array $payment_providers = [];
	public string $mollie_profile_id;
	public string $stripe_public_key;
	public bool $is_test_mode;

	public function __construct()
	{
		$this->is_offline = Configs::getValueAsString('webshop_offline');
		$this->currency = Configs::getValueAsString('webshop_currency');
		$this->allow_guest_checkout = Configs::getValueAsBool('webshop_allow_guest_checkout');
		$this->terms_url = Configs::getValueAsString('webshop_terms_url');
		$this->privacy_url = Configs::getValueAsString('webshop_privacy_url');
		$this->payment_providers = (new OmnipayFactory())->get_supported_providers();

		$this->mollie_profile_id = config('omnipay.Mollie.profileId', '');
		// Disable Stripe. It is not working yet, maybe later.
		$this->stripe_public_key = ''; // config('omnipay.Stripe.publishable_key', '');
		$this->is_test_mode = config('omnipay.testMode', false) === true;
	}
}