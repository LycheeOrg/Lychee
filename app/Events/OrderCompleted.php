<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when an order payment is completed successfully.
 *
 * Dispatched after payment confirmation to trigger order fulfillment
 * and related post-payment processing.
 *
 * @see OrderCompletedListener Handles automatic order fulfillment
 */
class OrderCompleted
{
	use Dispatchable;
	use InteractsWithSockets;
	use SerializesModels;

	/**
	 * Create a new OrderCompleted event instance.
	 *
	 * @param int $order_id The ID of the completed order
	 */
	public function __construct(public int $order_id)
	{
	}
}
