<?php

namespace App\Exceptions;

use League\Flysystem\FilesystemException;
use Symfony\Component\HttpFoundation\Response;

/**
 * FlySystemLycheeException.
 *
 * Returns status code 500 to an HTTP client.
 */
class FlySystemLycheeException extends BaseLycheeException implements FilesystemException
{
	public function __construct(string $msg, \Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_INTERNAL_SERVER_ERROR, $msg, $previous);
	}
}
