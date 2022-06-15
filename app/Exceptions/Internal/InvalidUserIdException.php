<?php

namespace App\Exceptions\Internal;

class InvalidUserIdException extends LycheeDomainException
{
	public function __construct(\Throwable $previous)
	{
		parent::__construct('Invalid user ID', $previous);
	}
}
