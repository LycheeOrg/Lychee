<?php

declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class InstallationAlreadyCompletedException extends BaseLycheeException
{
	public function __construct(?\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_FORBIDDEN, 'Installation has already been run', $previous);
	}
}
