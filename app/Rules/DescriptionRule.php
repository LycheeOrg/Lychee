<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Rules;

final class DescriptionRule extends StringRule
{
	public function __construct()
	{
		parent::__construct(true, 1000);
	}
}
