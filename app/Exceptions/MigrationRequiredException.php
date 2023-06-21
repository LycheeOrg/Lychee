<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class MigrationRequiredException extends BaseLycheeException
{
	public function __construct(?\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_SERVICE_UNAVAILABLE, 'Database version is behind, please apply migration', $previous);
	}
}
