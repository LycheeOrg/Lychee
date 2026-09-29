<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\V3;

use App\Http\Resources\GalleryConfigs\RootConfig;
use App\Http\Resources\Rights\RootAlbumRightsResource;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Response body of `GET /api/v3/Albums/root/config`: the root gallery
 * page's configuration and rights, built from config values and the
 * current user only (no album query).
 */
#[TypeScript()]
class AlbumRootConfigResource extends Data
{
	public RootConfig $config;
	public RootAlbumRightsResource $rights;

	public function __construct()
	{
		$this->config = new RootConfig();
		$this->rights = new RootAlbumRightsResource();
	}
}
