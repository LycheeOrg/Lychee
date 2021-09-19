<?php

namespace App\Exceptions\Internal;

class InvalidUserIdException extends LycheeDomainException
{
	public function __construct()
	{
		parent::__construct('Invalid user ID');
	}
}
