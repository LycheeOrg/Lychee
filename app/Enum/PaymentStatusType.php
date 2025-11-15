<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Enum;

use App\Enum\Traits\DecorateBackedEnum;

/**
 * Enum PaymentStatusType.
 *
 * Status of a payment transaction.
 */
enum PaymentStatusType: string
{
	use DecorateBackedEnum;

	// States where we can trigger the payment
	case PENDING = 'pending'; 	// Initial state
	case CANCELLED = 'cancelled'; 	// Payment aborted by user
	case FAILED = 'failed'; 	// Payment failed

	// Not implemented yet.
	case REFUNDED = 'refunded';

	// Intermediate state during payment processing
	case PROCESSING = 'processing';

	// When processing is done ofline.
	// In such case we do not go through the full flow.
	case OFFLINE = 'offline';

	// Final payment state
	case COMPLETED = 'completed';

	// The order is closed for any further action: paid and delivered.
	case CLOSED = 'closed';

	public function canCheckout()
	{
		return match ($this) {
			self::PENDING, self::FAILED, self::CANCELLED => true,
			default => false,
		};
	}
}
