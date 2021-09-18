<?php

namespace App\Exceptions\Internal;

class IllegalOrderOfOperationException extends \LogicException
{
	public function __construct(string $msg)
	{
		parent::__construct($msg);
	}
}
