<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Exceptions;

use Exception;

/**
 * Exception thrown when LDAP authentication fails.
 *
 * This includes invalid credentials, bind failures, and user not found.
 */
class LdapAuthenticationException extends \Exception
{
	/**
	 * Create a new exception instance.
	 *
	 * @param string          $message  User-friendly error message
	 * @param \Throwable|null $previous Underlying exception
	 */
	public function __construct(string $message = 'LDAP authentication failed', ?\Throwable $previous = null)
	{
		parent::__construct($message, 0, $previous);
	}
}
