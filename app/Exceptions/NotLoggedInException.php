<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class NotLoggedInException extends Exception
{
	public function __construct(
		$code = 0,
		Throwable $previous = null
	) {
		parent::__construct('Request Session data while not being logged in.', $code, $previous);
	}
}
