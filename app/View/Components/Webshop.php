<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\View\Components;

use App\Exceptions\ConfigurationKeyMissingException;
use App\Models\Configs;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\View\Component;
use Illuminate\View\View;
use LycheeVerify\Verify;

/**
 * This is the bottom of the page.
 * We provides socials etc...
 */
class Webshop extends Component
{
	public bool $enable = false;
	public bool $with_mollie = false;
	public bool $with_stripe = false;

	/**
	 * Initialize the footer once for all.
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	public function __construct()
	{
		$is_supporter = resolve(Verify::class)->check();

		$this->enable = $is_supporter && !Configs::getValueAsBool('webshop_offline') && Configs::getValueAsBool('webshop_enabled');
		$this->with_mollie = config('omnipay.Mollie.apiKey') !== '';
		$this->with_stripe = config('omnipay.Stripe.apiKey') !== '';
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