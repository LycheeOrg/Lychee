<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions\Internal;

class InvalidSizeVariantException extends LycheeDomainException
{
	public function __construct(string $msg)
	{
		parent::__construct($msg);
	}
}