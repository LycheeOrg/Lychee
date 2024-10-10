<?php

namespace App\Contracts\Http\Requests;

use App\Models\AccessPermission;

interface HasAccessPermission
{
	/**
	 * @return AccessPermission
	 */
	public function perm(): AccessPermission;
}
