<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Rules;

final class PasswordRule extends StringRule
{
	public function __construct(bool $is_nullable)
	{
		parent::__construct($is_nullable, 100);
	}
}
