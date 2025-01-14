<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Diagnostics;

use App\Models\Album;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class AlbumTree extends Data
{
	public function __construct(
		public string $id,
		public string $title,
		public ?string $parent_id,
		public int $_lft,
		public int $_rgt,
	) {
	}

	public static function FromModel(Album $album): AlbumTree
	{
		return new self(
			$album->id,
			$album->title,
			$album->parent_id,
			$album->_lft,
			$album->_rgt
		);
	}
}
