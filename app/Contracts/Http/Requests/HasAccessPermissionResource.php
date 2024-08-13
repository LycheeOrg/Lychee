<?php

namespace App\Contracts\Http\Requests;

use App\Http\Resources\Models\AccessPermissionResource;

interface HasAccessPermissionResource
{
	/**
	 * @return AccessPermissionResource
	 */
	public function perm(): AccessPermissionResource;
}
