<?php

declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * InsufficientEntropyException.
 *
 * Returns status code 503 (Service unavailable) to an HTTP client.
 */
class InsufficientEntropyException extends BaseLycheeException
{
	public function __construct(?\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_SERVICE_UNAVAILABLE, 'Insufficient entropy', $previous);
	}
}