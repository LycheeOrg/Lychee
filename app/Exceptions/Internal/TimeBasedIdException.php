<?php

namespace App\Exceptions\Internal;

use App\Contracts\Exceptions\InternalLycheeException;

class TimeBasedIdException extends \RuntimeException implements InternalLycheeException
{
	public function __construct(string $msg, \Throwable $previous = null)
	{
		parent::__construct($msg, 0, $previous);
	}
}
