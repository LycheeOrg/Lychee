<?php

namespace App\Exceptions\Internal;

class InvalidSizeVariantException extends LycheeDomainException
{
	public function __construct(string $msg)
	{
		parent::__construct($msg);
	}
}