<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions\Internal;

class InvalidSmartIdException extends LycheeDomainException
{
	public function __construct(string $invalidID)
	{
		parent::__construct('Invalid smart ID: ' . $invalidID);
	}
}
