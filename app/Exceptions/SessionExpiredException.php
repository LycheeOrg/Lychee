<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions;

class SessionExpiredException extends BaseLycheeException
{
	/** @var int HTTP_PAGE_EXPIRED proprietary Laravel HTTP status code to indicate session expiration */
	public const HTTP_PAGE_EXPIRED = 419;

	public const DEFAULT_MESSAGE = 'Session expired';

	public function __construct(string $msg = self::DEFAULT_MESSAGE, ?\Throwable $previous = null)
	{
		parent::__construct(self::HTTP_PAGE_EXPIRED, $msg, $previous);
	}
}
