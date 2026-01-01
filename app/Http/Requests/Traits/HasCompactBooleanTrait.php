<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

trait HasCompactBooleanTrait
{
	protected bool $is_compact;

	public function is_compact(): bool
	{
		return $this->is_compact;
	}
}
