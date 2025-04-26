<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions\Internal;

class ZeroModuloException extends LycheeDomainException
{
	public function __construct()
	{
		parent::__construct('Modulo equals zero');
	}
}
