<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * ConfigurationException.
 *
 * Returns status code 412 (Precondition failed) to an HTTP client.
 */
class ConfigurationException extends BaseLycheeException
{
	public function __construct(string $msg, ?\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_PRECONDITION_FAILED, $msg, $previous);
	}
}
