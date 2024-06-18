<?php

declare(strict_types=1);

namespace App\Exceptions\Internal;

class IllegalOrderOfOperationException extends LycheeLogicException
{
	public function __construct(string $msg)
	{
		parent::__construct($msg);
	}
}
