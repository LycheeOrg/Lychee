<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * UnauthorizedException.
 *
 * Returns status code 403 (Forbidden) to an HTTP client.
 */
class UnauthorizedException extends BaseLycheeException
{
	public const DEFAULT_MESSAGE = 'Insufficient privileges';

	public function __construct(string $msg = self::DEFAULT_MESSAGE, \Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_FORBIDDEN, $msg, $previous);
	}
}