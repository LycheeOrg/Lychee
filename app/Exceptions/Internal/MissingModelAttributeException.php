<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions\Internal;

class MissingModelAttributeException extends FailedModelAssumptionException
{
	public function __construct(string $modelName, string $attributeName)
	{
		parent::__construct('Attribute/column "' . $attributeName . '" for model "' . $modelName . '" missing');
	}
}
