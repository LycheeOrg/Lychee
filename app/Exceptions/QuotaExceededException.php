<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * QuotaExceededException.
 *
 * Returns status code 413 (Payload Too Large) to an HTTP client.
 */
class QuotaExceededException extends BaseLycheeException
{
	public const DEFAULT_MESSAGE = 'You have reached your quota.';

	public function __construct(string $message = self::DEFAULT_MESSAGE, ?\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_REQUEST_ENTITY_TOO_LARGE, $message, $previous);
	}
}
