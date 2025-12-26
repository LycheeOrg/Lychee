<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Enum;

use App\Enum\Traits\DecorateBackedEnum;

/**
 * Enum OmnipayProviderType.
 *
 * Available providers
 */
enum OmnipayProviderType: string
{
	use DecorateBackedEnum;

	case DUMMY = 'Dummy';
	case MOLLIE = 'Mollie';
	case PAYPAL = 'PayPal';
	case STRIPE = 'Stripe';

	/**
	 * Determine if the provider is allowed in the current environment.
	 *
	 * @return bool
	 */
	public function isAllowed(): bool
	{
		if ($this === self::DUMMY) {
			return config('app.env', 'production') !== 'production';
		}

		return true;
	}

	/**
	 * Get the required configuration keys for the given provider.
	 *
	 * @return string[]
	 */
	public function requiredKeys(): array
	{
		return match ($this) {
			OmnipayProviderType::DUMMY => ['apiKey'],
			OmnipayProviderType::MOLLIE => ['apiKey', 'profileId'],
			OmnipayProviderType::STRIPE => ['apiKey', 'publishableKey', 'disabled'], // we set disabled to prevent it from showing up.
			OmnipayProviderType::PAYPAL => ['clientId', 'secret'],
		};
	}
}
