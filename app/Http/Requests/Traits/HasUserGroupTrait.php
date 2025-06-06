<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

use App\Models\UserGroup;

trait HasUserGroupTrait
{
	protected UserGroup $user_group;

	public function user_group(): UserGroup
	{
		return $this->user_group;
	}
}
