<?php

namespace App\Exceptions\Internal;

use App\Contracts\InternalLycheeException;

class LycheeInvalidArgumentException extends \InvalidArgumentException implements InternalLycheeException
{
	public function __construct(string $msg, \Throwable $previous = null)
	{
		parent::__construct($msg, 0, $previous);
	}
}
