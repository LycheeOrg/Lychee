<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Search\Strategies;

use App\Contracts\Search\PhotoSearchTokenStrategy;
use App\DTO\Search\SearchToken;
use App\Enum\SizeVariantType;
use Illuminate\Database\Eloquent\Builder;

/**
 * Handles `ratio:` search tokens.
 *
 * Named buckets:
 *   ratio:landscape  → size_variants.ratio > 1.05
 *   ratio:portrait   → size_variants.ratio < 0.95
 *   ratio:square     → 0.95 <= size_variants.ratio <= 1.05
 *
 * Numeric comparison:
 *   ratio:>1.5       → size_variants.ratio > 1.5
 *   ratio:<=0.75     → size_variants.ratio <= 0.75
 *
 * Uses whereHas on size_variants filtered to ORIGINAL type (type=0) to avoid
 * row multiplication from thumbnail variants (NFR-027-03).
 */
class RatioStrategy implements PhotoSearchTokenStrategy
{
	private const BUCKET_LOW = 0.95;
	private const BUCKET_HIGH = 1.05;

	public function apply(Builder $query, SearchToken $token): void
	{
		$original = SizeVariantType::ORIGINAL->value;

		if ($token->operator === null) {
			// Named bucket.
			$this->applyBucket($query, $token->value, $original);
		} else {
			// Numeric comparison.
			$ratio = (float) $token->value;
			$op = $token->operator;
			$query->whereHas(
				'size_variants',
				fn (Builder $sq) => $sq->where('type', $original)->where('ratio', $op, $ratio)
			);
		}
	}

	private function applyBucket(Builder $query, string $bucket, int $original): void
	{
		match ($bucket) {
			'landscape' => $query->whereHas('size_variants', fn (Builder $sq) => $sq->where('type', $original)->where('ratio', '>', self::BUCKET_HIGH)),
			'portrait' => $query->whereHas('size_variants', fn (Builder $sq) => $sq->where('type', $original)->where('ratio', '<', self::BUCKET_LOW)),
			'square' => $query->whereHas(
				'size_variants',
				fn (Builder $sq) => $sq->where('type', $original)
					->where('ratio', '>=', self::BUCKET_LOW)
					->where('ratio', '<=', self::BUCKET_HIGH)
			),
			default => null, // Already validated by the parser; should never reach here.
		};
	}
}
