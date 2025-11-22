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

	// Intermediate state during payment processing (call the payment provider is being made)
	case PROCESSING = 'processing';

	// When processing is done offline.
	// In this case we do not go through the full flow.
	case OFFLINE = 'offline';

	// Final payment state
	case COMPLETED = 'completed';

	// The order is closed for any further action: paid and delivered.
	case CLOSED = 'closed';

	/**
	 * Validate whether the order can be checkout (start processing the payment)
	 * This requires one of the following state:
	 * - pending (payment never attempted)
	 * - failed (payment attempted but rejected)
	 * - cancelled (payment attempted but cancelled).
	 *
	 * @return bool
	 */
	public function canCheckout(): bool
	{
		return match ($this) {
			self::PENDING, self::FAILED, self::CANCELLED => true,
			default => false,
		};
	}

	/**
	 * We can add items to the order only in the following states:
	 * - pending (payment never attempted)
	 * - failed (payment attempted but rejected)
	 * - cancelled (payment attempted but cancelled)
	 *
	 * Any other state would mean that the user could gain access to content they should not have.
	 * It matches the canCheckout function by coincidence. We do not want to merge the code to ensure clarity.
	 *
	 * @return bool
	 */
	public function canAddItems(): bool
	{
		return match ($this) {
			self::PENDING, self::FAILED, self::CANCELLED => true,
			default => false,
		};
	}
}
