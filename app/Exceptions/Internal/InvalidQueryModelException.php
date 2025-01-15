<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions\Internal;

class InvalidQueryModelException extends LycheeInvalidArgumentException
{
	public function __construct(string $modelName)
	{
		parent::__construct('The query does not query for ' . $modelName . 's');
	}
}
