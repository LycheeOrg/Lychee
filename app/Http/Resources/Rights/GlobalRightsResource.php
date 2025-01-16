<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Rights;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class GlobalRightsResource extends Data
{
	public RootAlbumRightsResource $root_album;
	public SettingsRightsResource $settings;
	public UserManagementRightsResource $user_management;
	public UserRightsResource $user;

	public function __construct()
	{
		$this->root_album = new RootAlbumRightsResource();
		$this->settings = new SettingsRightsResource();
		$this->user_management = new UserManagementRightsResource();
		$this->user = new UserRightsResource();
	}
}
