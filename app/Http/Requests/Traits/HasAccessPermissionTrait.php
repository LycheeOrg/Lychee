<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

use App\Models\AccessPermission;

trait HasAccessPermissionTrait
{
	protected AccessPermission $perm;

	/**
	 * @return AccessPermission
	 */
	public function perm(): AccessPermission
	{
		return $this->perm;
	}
}
