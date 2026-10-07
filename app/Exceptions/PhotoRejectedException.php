<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * PhotoRejectedException.
 *
 * Returns status code 409 (Conflict) to an HTTP client.
 */
class PhotoRejectedException extends BaseLycheeException
{
	public const DEFAULT_MESSAGE = 'The photo has been rejected';

	public function __construct(string $message = self::DEFAULT_MESSAGE, ?\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_CONFLICT, $message, $previous);
	}
}
