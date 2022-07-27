<?php

namespace App\Exceptions\Internal;

class IllegalOrderOfOperationException extends LycheeLogicException
{
	public function __construct(string $msg)
	{
		parent::__construct($msg);
	}
}
