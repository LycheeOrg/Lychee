<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * ConfigurationException.
 *
 * Returns status code 412 (Precondition failed) to an HTTP client.
 */
class ConfigurationException extends BaseLycheeException
{
	public function __construct(string $msg, ?\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_PRECONDITION_FAILED, $msg, $previous);
	}
}
