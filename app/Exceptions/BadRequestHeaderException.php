<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class BadRequestHeaderException extends BaseLycheeException
{
	public function __construct(string $msg, \Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_BAD_REQUEST, $msg, $previous);
	}
}
