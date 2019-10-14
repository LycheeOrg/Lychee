<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class NotInCacheException extends Exception
{
	public function __construct(
		$code = 0,
		Throwable $previous = null
	) {
		parent::__construct('Data not in Cache', $code, $previous);
	}
}