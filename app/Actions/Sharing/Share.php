<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Sharing;

use App\Http\Resources\Models\AccessPermissionResource;
use App\Models\AccessPermission;

class Share
{
	/**
	 * Create an access permission from a resource.
	 *
	 * @param AccessPermissionResource $access_permission_resource
	 * @param int|null                 $user_id
	 * @param int|null                 $user_group_id
	 * @param string                   $base_album_id
	 *
	 * @return AccessPermission
	 */
	public function do(
		AccessPermissionResource $access_permission_resource,
		string $base_album_id,
		?int $user_id = null,
		?int $user_group_id = null,
	): AccessPermission {
		$perm = new AccessPermission();
		$perm->user_id = $user_id;
		$perm->user_group_id = $user_group_id;
		$perm->base_album_id = $base_album_id;
		$perm->grants_full_photo_access = $access_permission_resource->grants_full_photo_access;
		$perm->grants_download = $access_permission_resource->grants_download;
		$perm->grants_upload = $access_permission_resource->grants_upload;
		$perm->grants_edit = $access_permission_resource->grants_edit;
		$perm->grants_delete = $access_permission_resource->grants_delete;
		$perm->load('user');
		$perm->load('album');
		$perm->load('user_group');
		$perm->save();

		return $perm;
	}
}