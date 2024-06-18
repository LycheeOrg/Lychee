<?php

declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * ExternalComponentFailedException.
 *
 * Returns status code 503 (Service unavailable) to an HTTP client.
 */
class ExternalComponentFailedException extends BaseLycheeException
{
	public function __construct(string $msg, ?\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_SERVICE_UNAVAILABLE, $msg, $previous);
	}
}