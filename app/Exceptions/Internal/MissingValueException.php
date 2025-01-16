<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions\Internal;

class MissingValueException extends LycheeDomainException
{
	public function __construct(string $parameterName)
	{
		parent::__construct('Value for "' . $parameterName . '" must not be empty');
	}
}
