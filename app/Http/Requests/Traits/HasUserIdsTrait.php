<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

trait HasUserIdsTrait
{
	/**
	 * @var array<int>
	 */
	protected array $user_ids = [];

	/**
	 * @return array<int>
	 */
	public function userIds(): array
	{
		return $this->user_ids;
	}
}
