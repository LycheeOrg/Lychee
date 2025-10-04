<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions\Shop;

use App\Exceptions\BaseLycheeException;
use Symfony\Component\HttpFoundation\Response;

class PhotoNotPurchasableException extends BaseLycheeException
{
	public function __construct()
	{
		parent::__construct(
			Response::HTTP_FORBIDDEN,
			'Photo is not available for purchase'
		);
	}
}