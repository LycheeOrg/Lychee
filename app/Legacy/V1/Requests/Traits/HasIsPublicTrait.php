<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Traits;

trait HasIsPublicTrait
{
	protected bool $is_public = false;

	/**
	 * @return bool
	 */
	public function is_public(): bool
	{
		return $this->is_public;
	}
}
