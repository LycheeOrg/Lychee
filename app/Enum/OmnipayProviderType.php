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
	case PAYPAL_EXPRESS = 'PayPal_Express';
	case PAYPAL_EXPRESSINCONTEXT = 'PayPal_ExpressInContext';
	case PAYPAL_PRO = 'PayPal_Pro';
	case PAYPAL_REST = 'PayPal_Rest';
	case STRIPE = 'Stripe';
}
