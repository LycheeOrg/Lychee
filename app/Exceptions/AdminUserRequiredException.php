<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class AdminUserRequiredException extends BaseLycheeException
{
	public function __construct(?\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_SERVICE_UNAVAILABLE, 'Admin user not found', $previous);
	}
}
