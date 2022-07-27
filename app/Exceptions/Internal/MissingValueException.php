<?php

namespace App\Exceptions\Internal;

class MissingValueException extends LycheeDomainException
{
	public function __construct(string $parameterName)
	{
		parent::__construct('Value for "' . $parameterName . '" must not be empty');
	}
}
