<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions\Shop;

use App\Exceptions\BaseLycheeException;
use Symfony\Component\HttpFoundation\Response;

class BasketDeletionFailedException extends BaseLycheeException
{
	/**
	 * Create a new BasketDeletionFailedException instance.
	 *
	 * @param int         $basket_id The ID of the basket that failed to delete
	 * @param string|null $reason    Optional reason for the failure
	 */
	public function __construct(int $basket_id, ?string $reason = null)
	{
		$message = sprintf('Failed to delete basket #%d', $basket_id);
		if ($reason !== null) {
			$message .= sprintf(': %s', $reason);
		}

		parent::__construct(Response::HTTP_INTERNAL_SERVER_ERROR, $message);
	}
}
