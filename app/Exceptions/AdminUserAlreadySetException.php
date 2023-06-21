<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class AdminUserAlreadySetException extends BaseLycheeException
{
	public function __construct(?\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_FORBIDDEN, 'Admin User has already been set', $previous);
	}
}
