<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

use App\Enum\UserGroupRole;

trait HasRoleTrait
{
	protected UserGroupRole $role;

	public function role(): UserGroupRole
	{
		return $this->role;
	}
}
