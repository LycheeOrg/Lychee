<?php

namespace App\Exceptions\Internal;

use Throwable;

class JsonRequestFailedException extends \RuntimeException
{
	public function __construct(string $msg, Throwable $previous = null)
	{
		parent::__construct($msg, 0, $previous);
	}
}
