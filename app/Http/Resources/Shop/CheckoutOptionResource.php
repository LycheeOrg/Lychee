<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Shop;

use App\Factories\OmnipayFactory;
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
	public string $paypal_client_id;
	public bool $is_test_mode;
	public bool $is_lycheeorg_disclaimer_enabled;

	public function __construct()
	{
		$this->is_offline = request()->configs()->getValueAsBool('webshop_offline');
		$this->currency = request()->configs()->getValueAsString('webshop_currency');
		$this->allow_guest_checkout = request()->configs()->getValueAsBool('webshop_allow_guest_checkout');
		$this->terms_url = request()->configs()->getValueAsString('webshop_terms_url');
		$this->privacy_url = request()->configs()->getValueAsString('webshop_privacy_url');
		$this->is_lycheeorg_disclaimer_enabled = request()->configs()->getValueAsBool('webshop_lycheeorg_disclaimer_enabled');
		$this->payment_providers = (new OmnipayFactory())->get_supported_providers();

		$this->mollie_profile_id = config('omnipay.Mollie.profileId', '');
		// Disable Stripe. It is not working yet, maybe later.
		$this->stripe_public_key = ''; // config('omnipay.Stripe.publishableKey', '');
		$this->paypal_client_id = config('omnipay.PayPal.clientId', '');
		$this->is_test_mode = config('omnipay.testMode', false) === true;
	}
}