<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Rules;

class PasswordRule extends StringRule
{
	public function __construct(bool $isNullable)
	{
		parent::__construct($isNullable, 100);
	}
}
