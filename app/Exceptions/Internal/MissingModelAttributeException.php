<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions\Internal;

class MissingModelAttributeException extends FailedModelAssumptionException
{
	public function __construct(string $model_name, string $attribute_name)
	{
		parent::__construct('Attribute/column "' . $attribute_name . '" for model "' . $model_name . '" missing');
	}
}
