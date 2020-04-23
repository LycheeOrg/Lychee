<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class ComposerNotAvailableException extends Exception
{
	public function __construct(
		$code = 0,
		Throwable $previous = null
	) {
		parent::__construct('composer (software) is not available.', $code, $previous);
	}
}