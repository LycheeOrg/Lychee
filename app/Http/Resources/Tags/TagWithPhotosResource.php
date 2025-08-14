<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Tags;

use App\Http\Resources\Models\PhotoResource;
use App\Http\Resources\Traits\HasPrepPhotoCollection;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class TagWithPhotosResource extends Data
{
	use HasPrepPhotoCollection;

	/**
	 * @param Collection<int,PhotoResource> $photos
	 */
	public function __construct(
		public int $id,
		public string $name,
		public Collection $photos,
	) {
		$this->prepPhotosCollection();
	}
}
