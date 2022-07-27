<?php

namespace App\Exceptions\Internal;

class NotImplementedException extends LycheeLogicException
{
	public function __construct(string $msg)
	{
		parent::__construct($msg);
	}
}
