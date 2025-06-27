<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Timeline;

use App\Enum\PhotoLayoutType;
use App\Http\Resources\GalleryConfigs\RootConfig;
use App\Http\Resources\Rights\RootAlbumRightsResource;
use App\Models\Configs;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Initialization resource for the search.
 */
#[TypeScript()]
class InitResource extends Data
{
	public PhotoLayoutType $photo_layout;
	public bool $is_timeline_page_enabled = true;
	public RootConfig $config;
	public RootAlbumRightsResource $rights;

	public function __construct()
	{
		$this->photo_layout = Configs::getValueAsEnum('timeline_photos_layout', PhotoLayoutType::class);
		$this->is_timeline_page_enabled = Configs::getValueAsBool('timeline_page_enabled');
		$this->config = new RootConfig();
		$this->rights = new RootAlbumRightsResource();
	}
}