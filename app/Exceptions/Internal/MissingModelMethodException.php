<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions\Internal;

class MissingModelMethodException extends FailedModelAssumptionException
{
	public function __construct(string $modelName, string $methodName)
	{
		parent::__construct('Method "' . $methodName . '" for model "' . $modelName . '" missing');
	}
}
