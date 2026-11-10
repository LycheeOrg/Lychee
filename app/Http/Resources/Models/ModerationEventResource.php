<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Enum\ModerationEvent;
use App\Enum\ModerationSource;
use App\Models\PhotoModerationEvent;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * One entry of the moderation history (Feature 089, FR-089-05).
 *
 * Expects `photo.size_variants`, `photo.owner` and `user` to be eager loaded.
 */
#[TypeScript()]
class ModerationEventResource extends Data
{
	public int $id;
	public string $photo_id;
	public string $photo_title;
	public ?string $thumb_url;
	public string $owner_username;
	public ModerationEvent $event;
	public ModerationSource $source;
	/** The acting admin; null for the system or a deleted admin. */
	public ?string $username;
	public string $created_at;

	public function __construct(PhotoModerationEvent $event)
	{
		$this->id = $event->id;
		$this->photo_id = $event->photo_id;
		$this->photo_title = $event->photo->title;
		$this->thumb_url = $event->photo->size_variants->getThumb()?->url;
		$this->owner_username = $event->photo->owner?->username ?? '';
		$this->event = $event->event;
		$this->source = $event->source;
		$this->username = $event->user?->username;
		$this->created_at = $event->created_at->toIso8601String();
	}
}
