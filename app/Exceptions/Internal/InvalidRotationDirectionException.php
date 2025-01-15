<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions\Internal;

class InvalidRotationDirectionException extends LycheeDomainException
{
	public function __construct()
	{
		parent::__construct('Rotation direction must either equal -1 or 1');
	}
}