<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class InstallationFailedException extends BaseLycheeException
{
	public function __construct(string $msg, \Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_INTERNAL_SERVER_ERROR, $msg, $previous);
	}
}
