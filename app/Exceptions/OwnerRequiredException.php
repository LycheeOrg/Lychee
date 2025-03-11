<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * OwnerRequiredException.
 *
 * This exception is thrown, if a user tries to upload to an album which does not have an owner.
 */
class OwnerRequiredException extends BaseLycheeException
{
	public const DEFAULT_MESSAGE = 'Owner required to upload into albums.';

	public function __construct(string $msg = self::DEFAULT_MESSAGE, ?\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_CONFLICT, $msg, $previous);
	}
}
