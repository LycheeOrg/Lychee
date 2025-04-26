<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Rules;

final class UsernameRule extends StringRule
{
	public function __construct(bool $nullable = false)
	{
		parent::__construct($nullable, 100);
	}
}
