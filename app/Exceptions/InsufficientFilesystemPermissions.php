<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * InsufficientFilesystemPermissions.
 *
 * Returns status code 501 (Not implemented) to an HTTP client.
 */
class InsufficientFilesystemPermissions extends BaseLycheeException
{
	public function __construct(string $msg, ?\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_NOT_IMPLEMENTED, $msg, $previous);
	}
}
