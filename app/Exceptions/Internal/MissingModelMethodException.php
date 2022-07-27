<?php

namespace App\Exceptions\Internal;

class MissingModelMethodException extends FailedModelAssumptionException
{
	public function __construct(string $modelName, string $methodName)
	{
		parent::__construct('Method "' . $methodName . '" for model "' . $modelName . '" missing');
	}
}
