<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class GitNotAvailableException extends Exception
{
	public function __construct(
		$code = 0,
		Throwable $previous = null
	) {
		parent::__construct('git (software) is not available.', $code, $previous);
	}
}