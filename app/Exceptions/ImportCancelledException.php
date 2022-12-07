<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class ImportCancelledException extends BaseLycheeException
{
	public const DEFAULT_MESSAGE = 'Import cancelled';

	public function __construct(string $msg = self::DEFAULT_MESSAGE, \Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_INTERNAL_SERVER_ERROR, $msg, $previous);
	}
}
