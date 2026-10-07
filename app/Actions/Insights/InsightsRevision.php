<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Insights;

use App\DTO\Insights\InsightsScope;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Fingerprint of a scope's library (Feature 085, NFR-085-06, ADR-085-03):
 * count and latest `updated_at` of its photos and of its albums (regular
 * albums: tag albums never change a scope's photos). Any upload,
 * edit or deletion yields a new value.
 */
class InsightsRevision
{
	public function of(InsightsScope $scope): string
	{
		return implode('|', [
			self::fingerprint(InsightsScopeQuery::photos(DB::table('photos'), $scope), 'photos'),
			self::fingerprint(InsightsScopeQuery::albums(DB::table('albums')->join('base_albums', 'base_albums.id', '=', 'albums.id'), $scope), 'base_albums'),
		]);
	}

	private static function fingerprint(Builder $query, string $table): string
	{
		$row = $query->selectRaw('COUNT(*) AS total, MAX(' . $table . '.updated_at) AS latest')->first();

		return ($row?->total ?? 0) . '@' . ($row?->latest ?? '');
	}
}
