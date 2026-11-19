<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Insights;

use App\Enum\TimelineCategory;
use App\Enum\TimelineEventKind;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * A notable moment of the Insights timeline (Feature 085, FR-085-19).
 *
 * `date` is the local capture date `Y-m-d`. `subject` names the device of
 * device events; `value` holds the milestone number or the record value
 * (seconds, bytes, ISO, f-number, mm).
 */
#[TypeScript()]
class TimelineEventData extends Data
{
	public function __construct(
		public TimelineEventKind $kind,
		public TimelineCategory $category,
		public string $date,
		public ?string $subject = null,
		public int|float|null $value = null,
		public ?string $photo_id = null,
	) {
	}

	public static function of(TimelineEventKind $kind, string $date, ?string $subject = null, int|float|null $value = null, ?string $photo_id = null): self
	{
		return new self($kind, $kind->category(), $date, $subject, $value, $photo_id);
	}
}
