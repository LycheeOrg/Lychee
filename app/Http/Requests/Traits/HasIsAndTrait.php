<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

trait HasIsAndTrait
{
	protected bool $is_and;

	public function is_and(): bool
	{
		return $this->is_and;
	}
}