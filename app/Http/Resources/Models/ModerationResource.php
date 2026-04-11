<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Models\Photo;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * API resource for a photo awaiting moderation (is_validated = false).
 *
 * Only exposed to administrators via the moderation endpoints.
 */
#[TypeScript()]
class ModerationResource extends Data
{
	public string $photo_id;
	public string $title;
	public ?string $thumb_url;
	public string $owner_username;
	public ?string $album_title;
	public string $created_at;

	public function __construct(Photo $photo)
	{
		$this->photo_id = $photo->id;
		$this->title = $photo->title;
		$this->thumb_url = $photo->size_variants->getThumb()?->url;
		$this->owner_username = $photo->owner?->username ?? '';
		// Use the title of the first album the photo belongs to, if any
		$album = $photo->albums->first();
		$this->album_title = $album?->title;
		$this->created_at = $photo->created_at->toIso8601String();
	}
}
