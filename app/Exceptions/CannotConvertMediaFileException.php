<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * CannotConvertMediaFileException.
 *
 * Indicates that a media file cannot be converted to another format.
 */
class CannotConvertMediaFileException extends BaseLycheeException
{
	public const DEFAULT_MESSAGE = 'Cannot convert media file to another format';

	public function __construct(string $msg = self::DEFAULT_MESSAGE, ?\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_UNPROCESSABLE_ENTITY, $msg, $previous);
	}
}
