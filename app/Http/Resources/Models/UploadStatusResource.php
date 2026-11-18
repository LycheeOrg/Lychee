<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\DTO\UploadStatusEntry;
use App\Enum\UploadStatus;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Outcome of one upload, by its provisional ID (Feature 116, DO-116-03).
 *
 * `photo_id` is the photo to record; it can differ from `expected_id`
 * (duplicate, live-photo video joined to its still) and is null when the
 * caller may not see that photo.
 */
#[TypeScript()]
class UploadStatusResource extends Data
{
	public function __construct(
		public string $expected_id,
		public UploadStatus $status,
		public ?string $photo_id,
		public ?string $message,
	) {
	}

	/**
	 * @param string                 $expected_id        the provisional ID asked for
	 * @param UploadStatusEntry|null $entry              the entry readable by the caller, if any
	 * @param array<string,true>     $existing_photo_ids recorded photos that still exist
	 */
	public static function of(string $expected_id, ?UploadStatusEntry $entry, array $existing_photo_ids): self
	{
		if ($entry === null) {
			return new self($expected_id, UploadStatus::UNKNOWN, null, null);
		}

		$photo_id = isset($existing_photo_ids[$entry->photo_id ?? '']) ? $entry->photo_id : null;

		return new self($expected_id, $entry->status, $photo_id, $entry->message);
	}
}
