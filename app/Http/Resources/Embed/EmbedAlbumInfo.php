<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Embed;

use App\Models\Album;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Resource for embedding album data on external websites.
 *
 * Provides minimal album information and photo collection optimized for
 * external embedding. Includes only publicly visible data.
 */
#[TypeScript()]
class EmbedAlbumInfo extends Data
{
	public function __construct(
		public string $id,
		public string $title,
		public ?string $description,
		public int $photo_count,
		public ?string $copyright,
		public string $license,
	) {
	}

	/**
	 * Create resource from Album model.
	 *
	 * @param Album $album The album model
	 *
	 * @return self
	 */
	public static function fromModel(Album $album): self
	{
		return new self(
			id: $album->id,
			title: $album->title,
			description: $album->description,
			photo_count: $album->photos_count ?? $album->photos->count(),
			copyright: $album->copyright,
			license: $album->license?->localization(),
		);
	}
}
