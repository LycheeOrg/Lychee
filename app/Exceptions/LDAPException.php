<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class LDAPException.
 *
 * Indicates any error related to LDAP.
 * Returns status code 500 (Internal server error) to an HTTP client.
 *
 * Returns status code 500 (Internal server error) to an HTTP client.
 *
 * As this exception reports a 5xx code (opposed to a 4xx code) this
 * exception indicates a server-side error.
 * This means the failing operation is typically expected not to fail and
 * the client or user cannot do anything about it.
 */
class LDAPException extends LycheeBaseException
{
	public function __construct(string $msg, \Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_INTERNAL_SERVER_ERROR, $msg, $previous);
	}
}
