<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Constants;

class AccessPermissionConstants
{
	// computed table name
	public const COMPUTED_ACCESS_PERMISSIONS = 'computed_access_permissions';
	public const ACCESS_PERMISSIONS = 'access_permissions';

	// Id names
	public const BASE_ALBUM_ID = 'base_album_id';
	public const USER_ID = 'user_id';

	// Attributes name
	public const IS_LINK_REQUIRED = 'is_link_required';
	public const GRANTS_FULL_PHOTO_ACCESS = 'grants_full_photo_access';
	public const GRANTS_DOWNLOAD = 'grants_download';
	public const GRANTS_UPLOAD = 'grants_upload';
	public const GRANTS_EDIT = 'grants_edit';
	public const GRANTS_DELETE = 'grants_delete';
	public const PASSWORD = 'password';
}