<?php

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
