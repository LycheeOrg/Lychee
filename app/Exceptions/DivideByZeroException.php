<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class DivideByZeroException extends Exception
{
	public function __construct(
		$code = 0,
		Throwable $previous = null
	) {
		parent::__construct('gcd: Modulo by zero error.', $code, $previous);
	}
}
