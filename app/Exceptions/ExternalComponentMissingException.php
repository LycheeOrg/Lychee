<?php

declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * ExternalComponentMissingException.
 *
 * Returns status code 501 (Not implemented) to an HTTP client.
 */
class ExternalComponentMissingException extends BaseLycheeException
{
	public function __construct(string $msg, ?\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_NOT_IMPLEMENTED, $msg, $previous);
	}
}
