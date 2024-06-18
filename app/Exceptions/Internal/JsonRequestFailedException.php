<?php

declare(strict_types=1);

namespace App\Exceptions\Internal;

use App\Contracts\Exceptions\InternalLycheeException;

class JsonRequestFailedException extends \RuntimeException implements InternalLycheeException
{
	public function __construct(string $msg, ?\Throwable $previous = null)
	{
		parent::__construct($msg, 0, $previous);
	}
}
