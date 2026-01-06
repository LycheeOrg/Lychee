<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Exceptions\Shop;

use App\Exceptions\BaseLycheeException;
use Symfony\Component\HttpFoundation\Response;

class InvalidPurchaseOptionException extends BaseLycheeException
{
	public function __construct()
	{
		parent::__construct(
			Response::HTTP_FORBIDDEN,
			'Selected size and license combination is not available for purchase',
		);
	}
}