<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class ExecNotAvailable extends Exception
{
	public function __construct(
		$code = 0,
		Throwable $previous = null
	) {
		parent::__construct('exec is not available.', $code, $previous);
	}
}