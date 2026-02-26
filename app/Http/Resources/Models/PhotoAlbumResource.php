<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Models\Album;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class PhotoAlbumResource extends Data
{
	public string $id;
	public string $title;

	public function __construct(Album $album)
	{
		$this->id = $album->id;
		$this->title = $album->title;
	}
}
