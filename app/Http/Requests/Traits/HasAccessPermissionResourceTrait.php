<?php

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
