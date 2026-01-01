<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

trait HasIsPinnedTrait
{
	protected bool $is_pinned;

	public function is_pinned(): bool
	{
		return $this->is_pinned;
	}
}