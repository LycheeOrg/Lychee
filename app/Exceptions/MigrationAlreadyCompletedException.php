<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class MigrationAlreadyCompletedException extends BaseLycheeException
{
	public function __construct(?\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_FORBIDDEN, 'Migration has already been run', $previous);
	}
}
