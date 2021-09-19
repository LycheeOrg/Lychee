<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * UnauthorizedException.
 *
 * Returns status code 403 (Forbidden) to an HTTP client.
 */
class UnauthorizedException extends LycheeBaseException
{
	public function __construct(string $msg = 'User has insufficient privileges for this action', \Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_FORBIDDEN, $msg, $previous);
	}
}