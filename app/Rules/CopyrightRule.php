<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Rules;

final class CopyrightRule extends StringRule
{
	public function __construct()
	{
		parent::__construct(true, 300);
	}
}
