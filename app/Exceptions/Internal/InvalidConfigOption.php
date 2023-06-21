<?php

namespace App\Exceptions\Internal;

class InvalidConfigOption extends LycheeDomainException
{
	public function __construct(string $msg, ?\Throwable $previous = null)
	{
		parent::__construct($msg, $previous);
	}
}