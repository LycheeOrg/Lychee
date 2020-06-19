<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class RequestAdminDataException extends Exception
{
	public function __construct(
		$code = 0,
		Throwable $previous = null
	) {
		parent::__construct('Trying to get a User from Admin ID.', $code, $previous);
	}
}
