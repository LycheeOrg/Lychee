<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * TagAlreadyExistException.
 *
 * Returns status code 422 to an HTTP client.
 */
class TagAlreadyExistException extends BaseLycheeException
{
	public const DEFAULT_MESSAGE = 'Tag already exists.';

	public function __construct(string $message = self::DEFAULT_MESSAGE, ?\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_UNPROCESSABLE_ENTITY, $message, $previous);
	}
}
