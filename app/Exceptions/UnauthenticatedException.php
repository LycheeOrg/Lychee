<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * UnauthenticatedException.
 *
 * Returns status code 401 (Unauthorized) to an HTTP client.
 * _Note:_ Due to historic reasons the name of the HTTP Status Code 401
 * is "unauthorized", but actually means "unauthenticated".
 * So it is correct, to use 401 here.
 * Side remark: If one really wants to express that a user is unauthorized,
 * the suitable HTTP Status Code would equal 403 (Forbidden).
 */
class UnauthenticatedException extends BaseLycheeException
{
	public const DEFAULT_MESSAGE = 'User is not authenticated';

	public function __construct(string $msg = self::DEFAULT_MESSAGE, ?\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_UNAUTHORIZED, $msg, $previous);
	}
}
