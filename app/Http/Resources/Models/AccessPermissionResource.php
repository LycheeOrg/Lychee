<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Models\AccessPermission;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class AccessPermissionResource extends Data
{
	public function __construct(
		public ?int $id = null,
		public ?int $user_id = null,
		public ?string $username = null,
		public ?string $album_title = null,
		public ?string $album_id = null,
		public bool $grants_full_photo_access = false,
		public bool $grants_download = false,
		public bool $grants_upload = false,
		public bool $grants_edit = false,
		public bool $grants_delete = false,
	) {
	}

	public static function fromModel(AccessPermission $access_permission): AccessPermissionResource
	{
		return new AccessPermissionResource(
			id: $access_permission->id,
			user_id: $access_permission->user_id,
			username: $access_permission->user->name,
			album_title: $access_permission->album->title,
			album_id: $access_permission->base_album_id,
			grants_full_photo_access: $access_permission->grants_full_photo_access,
			grants_download: $access_permission->grants_download,
			grants_upload: $access_permission->grants_upload,
			grants_edit: $access_permission->grants_edit,
			grants_delete: $access_permission->grants_delete
		);
	}
}
