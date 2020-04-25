<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class NoOnlineUpdateException extends Exception
{
	public function __construct(
		$code = 0,
		Throwable $previous = null
	) {
		parent::__construct('Online updates are not allowed.', $code, $previous);
	}
}