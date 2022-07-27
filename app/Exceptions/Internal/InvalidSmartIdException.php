<?php

namespace App\Exceptions\Internal;

class InvalidSmartIdException extends LycheeDomainException
{
	public function __construct(string $invalidID)
	{
		parent::__construct('Invalid smart ID: ' . $invalidID);
	}
}
