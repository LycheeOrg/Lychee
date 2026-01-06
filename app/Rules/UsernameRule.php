<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Rules;

final class UsernameRule extends StringRule
{
	public function __construct(bool $nullable = false)
	{
		parent::__construct($nullable, 100);
	}
}
