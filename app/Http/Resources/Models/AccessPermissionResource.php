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

	public static function fromModel(AccessPermission $accessPermission): AccessPermissionResource
	{
		return new AccessPermissionResource(
			id: $accessPermission->id,
			user_id: $accessPermission->user_id,
			username: $accessPermission->user->name,
			album_title: $accessPermission->album->title,
			album_id: $accessPermission->base_album_id,
			grants_full_photo_access: $accessPermission->grants_full_photo_access,
			grants_download: $accessPermission->grants_download,
			grants_upload: $accessPermission->grants_upload,
			grants_edit: $accessPermission->grants_edit,
			grants_delete: $accessPermission->grants_delete
		);
	}
}
