<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Feature 069 (FR-069-03). The v3 search endpoint returns its whole result set
 * in one unpaginated response, so this key no longer describes a page size — it
 * becomes the hard ceiling on how many photo hits a single search may return
 * (ADR-0010's "capped with truncation" strategy).
 *
 * Renamed in place rather than dropped-and-recreated: the stored value has been
 * actively tuned (migration `2025_03_01_154728_search_pagination_limit_reduction`
 * cut the default from 1000 to 300), and `cat`/`type_range`/`order`/`level` are
 * all still correct for the new meaning.
 */
return new class() extends Migration {
	private const OLD_KEY = 'search_pagination_limit';
	private const NEW_KEY = 'search_result_limit';
	private const OLD_DESCRIPTION = 'Number of results to display per page.';
	private const NEW_DESCRIPTION = 'Maximum number of photo results returned by a single search.';

	public function up(): void
	{
		DB::table('configs')
			->where('key', '=', self::OLD_KEY)
			->update([
				'key' => self::NEW_KEY,
				'description' => self::NEW_DESCRIPTION,
			]);
	}

	public function down(): void
	{
		DB::table('configs')
			->where('key', '=', self::NEW_KEY)
			->update([
				'key' => self::OLD_KEY,
				'description' => self::OLD_DESCRIPTION,
			]);
	}
};
