<?php

declare(strict_types=1);

namespace App\Exceptions\Internal;

class MissingModelAttributeException extends FailedModelAssumptionException
{
	public function __construct(string $modelName, string $attributeName)
	{
		parent::__construct('Attribute/column "' . $attributeName . '" for model "' . $modelName . '" missing');
	}
}
