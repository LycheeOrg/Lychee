<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * UnauthenticatedException.
 *
 * Returns status code 401 (Unauthorized) to an HTTP client.
 * _Note:_ Due to historic reasons the name of the HTTP Status Code 401
 * is "unauthorized", but actually means "unauthenticated".
 * So it is correct, to use 401 here.
 * Side remark: If one really wants to express that a user is unauthorized,
 * the suitable HTTP Status Code would equal 403 (Forbidden).
 */
class UnauthenticatedException extends LycheeBaseException
{
	public function __construct(string $msg = 'User is not authenticated', \Throwable $previous = null)
	{
		// Note: Due to historic reasons the name of the HTTP Status Code 401
		// is "unauthorized", but actually means "unauthenticated".
		// So it is correct, to use HTTP_UNAUTHORIZED here.
		// Side remark: If one wants to express that a user is unauthorized,
		// the HTTP Status Code would equal 403 (HTTP_FORBIDDEN).
		parent::__construct(Response::HTTP_UNAUTHORIZED, $msg, $previous);
	}
}
