<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * InsufficientEntropyException.
 *
 * Returns status code 503 (Service unavailable) to an HTTP client.
 */
class InsufficientEntropyException extends BaseLycheeException
{
	public function __construct(?\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_SERVICE_UNAVAILABLE, 'Insufficient entropy', $previous);
	}
}