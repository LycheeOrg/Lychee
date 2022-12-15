<?php

namespace App\Exceptions\Internal;

use App\Contracts\Exceptions\InternalLycheeException;

class LycheeDomainException extends \DomainException implements InternalLycheeException
{
	public function __construct(string $msg, ?\Throwable $previous = null)
	{
		parent::__construct($msg, $previous !== null ? $previous->getCode() : 0, $previous);
	}
}
