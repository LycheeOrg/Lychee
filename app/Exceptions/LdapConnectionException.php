<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * Exception thrown when LDAP connection fails.
 *
 * This includes network timeouts, DNS failures, and TLS errors.
 */
class LdapConnectionException extends BaseLycheeException
{
	/**
	 * Create a new exception instance.
	 *
	 * @param string          $message  User-friendly error message
	 * @param \Throwable|null $previous Underlying exception
	 */
	public function __construct(string $message = 'Unable to connect to LDAP server', ?\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_INTERNAL_SERVER_ERROR, $message, $previous);
	}
}
