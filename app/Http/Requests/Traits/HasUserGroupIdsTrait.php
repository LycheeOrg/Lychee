<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

trait HasUserGroupIdsTrait
{
	/**
	 * @var array<int,int>
	 */
	protected array $user_group_ids = [];

	/**
	 * @return array<int,int>
	 */
	public function userGroupIds(): array
	{
		return $this->user_group_ids;
	}
}
