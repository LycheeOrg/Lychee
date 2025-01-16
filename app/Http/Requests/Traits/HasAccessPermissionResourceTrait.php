<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

use App\Http\Resources\Models\AccessPermissionResource;

trait HasAccessPermissionResourceTrait
{
	protected AccessPermissionResource $permResource;

	/**
	 * @return AccessPermissionResource
	 */
	public function permResource(): AccessPermissionResource
	{
		return $this->permResource;
	}
}
