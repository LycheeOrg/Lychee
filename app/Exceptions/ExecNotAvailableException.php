<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class ExecNotAvailableException extends Exception
{
	public function __construct(
		$code = 0,
		Throwable $previous = null
	) {
		parent::__construct('exec php function is not available.', $code, $previous);
	}
}