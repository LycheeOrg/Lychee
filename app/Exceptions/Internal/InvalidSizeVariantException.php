<?php

namespace App\Exceptions\Internal;

class InvalidSizeVariantException extends \InvalidArgumentException
{
	public function __construct(string $msg)
	{
		parent::__construct($msg);
	}
}