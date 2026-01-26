<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Exceptions;

use Exception;

/**
 * Exception thrown when LDAP connection fails.
 *
 * This includes network timeouts, DNS failures, and TLS errors.
 */
class LdapConnectionException extends \Exception
{
	/**
	 * Create a new exception instance.
	 *
	 * @param string          $message  User-friendly error message
	 * @param \Throwable|null $previous Underlying exception
	 */
	public function __construct(string $message = 'Unable to connect to LDAP server', ?\Throwable $previous = null)
	{
		parent::__construct($message, 0, $previous);
	}
}
