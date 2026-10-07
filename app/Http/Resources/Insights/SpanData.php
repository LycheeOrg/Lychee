<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Insights;

use App\Actions\Insights\Helpers\DateSpan;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * A streak or break between two local dates (Feature 085, FR-085-09).
 */
#[TypeScript()]
class SpanData extends Data
{
	public function __construct(
		public int $length,
		public string $from,
		public string $to,
	) {
	}

	public static function fromSpan(?DateSpan $span): ?self
	{
		return $span === null ? null : new self($span->length, $span->from, $span->to);
	}
}
