<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Http\Resources\GalleryConfigs\AlbumConfig;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class AbstractAlbumResource extends Data
{
	public function __construct(
		public AlbumConfig $config,
		public AlbumResource|SmartAlbumResource|TagAlbumResource|null $resource)
	{
	}
}
