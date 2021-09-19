<?php

namespace App\Exceptions\Internal;

use App\Contracts\InternalLycheeException;

class LycheeDomainException extends \DomainException implements InternalLycheeException
{
	public function __construct(string $msg, \Throwable $previous = null)
	{
		parent::__construct($msg, 0, $previous);
	}
}
