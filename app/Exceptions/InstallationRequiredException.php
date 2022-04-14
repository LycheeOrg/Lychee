<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class InstallationRequiredException extends LycheeBaseException
{
	public function __construct(\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_SERVICE_UNAVAILABLE, 'Installation not complete', $previous);
	}
}
