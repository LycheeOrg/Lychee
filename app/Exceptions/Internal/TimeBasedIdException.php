<?php

namespace App\Exceptions\Internal;

class TimeBasedIdException extends \RuntimeException
{
	public function __construct(string $msg, \Throwable $previous = null)
	{
		parent::__construct($msg, 0, $previous);
	}
}
