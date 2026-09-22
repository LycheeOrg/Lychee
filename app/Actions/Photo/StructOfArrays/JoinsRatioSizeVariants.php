<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\StructOfArrays;

use App\Eloquent\FixedQueryBuilder;
use App\Enum\SizeVariantType;
use App\Models\Photo;
use Illuminate\Database\Query\JoinClause;

/**
 * The three `type`-filtered `size_variants` `LEFT JOIN`s every tier-2
 * Struct-of-Arrays query needs to resolve a photo's aspect ratio, shared by
 * {@see QueryPhotoRatios} (Features 064/066/068) and
 * {@see \App\Actions\Search\StructOfArrays\QuerySearchPhotos} (Feature 069).
 *
 * Extracted verbatim from `QueryPhotoRatios` so the join aliases and the
 * `COALESCE(sv_original.ratio, sv_medium.ratio, sv_small.ratio, 1)` expression
 * that reads them cannot drift apart between tiers — the aliases are load-bearing
 * for that expression, so a second hand-written copy would be a silent-breakage
 * risk rather than harmless duplication.
 *
 * Deliberately does NOT reproduce {@see \App\Models\Photo::getAspectRatioAttribute()}'s
 * video-forces-1 special case: a video's real ratio wins whenever any of the
 * three exists (Feature 064, Q-064-07).
 */
trait JoinsRatioSizeVariants
{
	/**
	 * The `COALESCE` expression that reads the three joins below. Paired with
	 * them deliberately — never hand-write one without the other.
	 */
	private const RATIO_SELECT_RAW = 'COALESCE(sv_original.ratio, sv_medium.ratio, sv_small.ratio, 1) as ratio';

	/**
	 * @param FixedQueryBuilder<Photo> $query
	 */
	private function joinRatioSizeVariants(FixedQueryBuilder $query): void
	{
		$query->leftJoin('size_variants as sv_original', function (JoinClause $join): void {
			$join->on('sv_original.photo_id', '=', 'photos.id')->where('sv_original.type', '=', SizeVariantType::ORIGINAL->value);
		});
		$query->leftJoin('size_variants as sv_medium', function (JoinClause $join): void {
			$join->on('sv_medium.photo_id', '=', 'photos.id')->where('sv_medium.type', '=', SizeVariantType::MEDIUM->value);
		});
		$query->leftJoin('size_variants as sv_small', function (JoinClause $join): void {
			$join->on('sv_small.photo_id', '=', 'photos.id')->where('sv_small.type', '=', SizeVariantType::SMALL->value);
		});
	}
}
