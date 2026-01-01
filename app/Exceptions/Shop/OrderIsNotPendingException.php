<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Exceptions\Shop;

use App\Exceptions\BaseLycheeException;
use Symfony\Component\HttpFoundation\Response;

class OrderIsNotPendingException extends BaseLycheeException
{
	public function __construct(int $order_id)
	{
		parent::__construct(
			Response::HTTP_FORBIDDEN,
			sprintf('Order #%d is not in pending state and cannot be modified', $order_id),
		);
	}
}