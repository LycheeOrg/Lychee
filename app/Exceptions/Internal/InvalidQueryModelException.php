<?php

declare(strict_types=1);

namespace App\Exceptions\Internal;

class InvalidQueryModelException extends LycheeInvalidArgumentException
{
	public function __construct(string $modelName)
	{
		parent::__construct('The query does not query for ' . $modelName . 's');
	}
}
