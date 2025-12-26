<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\View\Components;

use App\Exceptions\ConfigurationKeyMissingException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Webshop component.
 * Displays webshop functionality when enabled for supporters.
 */
class Webshop extends Component
{
	public bool $enable = false;
	public bool $with_mollie = false;
	public bool $with_stripe = false;

	/**
	 * Initialize the webshop component.
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	public function __construct()
	{
		$is_supporter = request()->verify()->check();

		$this->enable = $is_supporter && !request()->configs()->getValueAsBool('webshop_offline') && request()->configs()->getValueAsBool('webshop_enabled');
		$this->with_mollie = config('omnipay.Mollie.apiKey') !== '' && config('omnipay.Mollie.apiKey') !== null;
		$this->with_stripe = config('omnipay.Stripe.apiKey') !== '' && config('omnipay.Stripe.apiKey') !== null;
	}

	/**
	 * Render component.
	 *
	 * @throws BindingResolutionException
	 */
	public function render(): View
	{
		return view('components.webshop');
	}
}