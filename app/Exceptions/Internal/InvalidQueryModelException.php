<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions\Internal;

class InvalidQueryModelException extends LycheeInvalidArgumentException
{
	public function __construct(string $model_name)
	{
		parent::__construct('The query does not query for ' . $model_name . 's');
	}
}
