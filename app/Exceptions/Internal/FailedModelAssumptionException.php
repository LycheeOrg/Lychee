<?php

declare(strict_types=1);

namespace App\Exceptions\Internal;

/**
 * Represents a failing assumption about a model.
 */
class FailedModelAssumptionException extends LycheeLogicException
{
	public function __construct(string $msg, ?\Throwable $previous = null)
	{
		parent::__construct($msg, $previous);
	}
}