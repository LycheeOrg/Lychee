<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * UnexpectedException.
 *
 * Last resort when nothing else matches.
 * Returns status code 500 (Internal Server Error) to an HTTP client.
 */
class UnexpectedException extends BaseLycheeException
{
	public function __construct(?\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_INTERNAL_SERVER_ERROR, 'Unknown Lychee exception (this is probably a bug)', $previous);
	}
}
