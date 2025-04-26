<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions\Internal;

class MissingValueException extends LycheeDomainException
{
	public function __construct(string $parameter_name)
	{
		parent::__construct('Value for "' . $parameter_name . '" must not be empty');
	}
}
