<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions\Internal;

class FeaturesDoesNotExistsException extends LycheeLogicException
{
	public function __construct(string $msg)
	{
		parent::__construct($msg);
	}
}
