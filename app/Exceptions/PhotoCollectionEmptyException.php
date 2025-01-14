<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * PhotoCollectionEmptyException.
 *
 * This exception is thrown, if a user request a frame but no pictures can be found.
 * We throw an error 500 because it is likely to be a server error.
 */
class PhotoCollectionEmptyException extends BaseLycheeException
{
	public const DEFAULT_MESSAGE = 'Photo collection is empty.';

	public function __construct(string $msg = self::DEFAULT_MESSAGE, ?\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_INTERNAL_SERVER_ERROR, $msg, $previous);
	}
}
