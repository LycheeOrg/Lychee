<?php

namespace App\Exceptions\Internal;

class InvalidUserIdException extends LycheeDomainException
{
	public function __construct(?\Throwable $previous = null)
	{
		parent::__construct('Invalid user ID', $previous);
	}
}
