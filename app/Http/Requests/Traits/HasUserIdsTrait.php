<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

trait HasUserIdsTrait
{
	/**
	 * @var array<int>
	 */
	protected array $userIds = [];

	/**
	 * @return array<int>
	 */
	public function userIds(): array
	{
		return $this->userIds;
	}
}
