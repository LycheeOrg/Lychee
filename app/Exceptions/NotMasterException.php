<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class NotMasterException extends Exception
{
	public function __construct(
		$code = 0,
		Throwable $previous = null
	) {
		parent::__construct('Branch is not master, cannot compare.', $code, $previous);
	}
}