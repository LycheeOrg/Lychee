<?php

declare(strict_types=1);

namespace App\Exceptions\Internal;

class FeaturesDoesNotExistsException extends LycheeLogicException
{
	public function __construct(string $msg)
	{
		parent::__construct($msg);
	}
}
