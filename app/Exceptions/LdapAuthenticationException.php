<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * Exception thrown when LDAP authentication fails.
 *
 * This includes invalid credentials, bind failures, and user not found.
 */
class LdapAuthenticationException extends BaseLycheeException
{
	/**
	 * Create a new exception instance.
	 *
	 * @param string          $message  User-friendly error message
	 * @param \Throwable|null $previous Underlying exception
	 */
	public function __construct(string $message = 'LDAP authentication failed', ?\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_FORBIDDEN, $message, $previous);
	}
}
