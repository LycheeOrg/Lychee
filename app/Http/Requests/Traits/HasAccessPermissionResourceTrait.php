<?php

namespace App\Http\Requests\Traits;

use App\Http\Resources\Models\AccessPermissionResource;

trait HasAccessPermissionResourceTrait
{
	protected AccessPermissionResource $perm;

	/**
	 * @return AccessPermissionResource
	 */
	public function perm(): AccessPermissionResource
	{
		return $this->perm;
	}
}
