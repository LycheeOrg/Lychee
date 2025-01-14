<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions;

use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * PasswordRequiredException.
 *
 * This exception is thrown, if a user tries to access a public,
 * password-protected album before the user has provided the password
 *
 * The status code 401 (Unauthorized) or 403 (Forbidden) which is sent to the
 * HTTP client depends on whether the user has already logged in or is
 * anonymous.
 */
class PasswordRequiredException extends BaseLycheeException
{
	public const DEFAULT_MESSAGE = 'Password required';

	public function __construct(string $msg = self::DEFAULT_MESSAGE, ?\Throwable $previous = null)
	{
		parent::__construct(Auth::check() ? Response::HTTP_FORBIDDEN : Response::HTTP_UNAUTHORIZED, $msg, $previous);
	}
}
