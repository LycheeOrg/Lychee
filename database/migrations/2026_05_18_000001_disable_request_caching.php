<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Force cache_enabled to 0.
 *
 * Request caching is now disabled by default and hidden from the settings UI
 * unless ENABLE_REQUEST_CACHING=true is set in the environment.  Any existing
 * installation that had the toggle on must have it silently turned off so that
 * the middleware no longer serves cached responses without the operator
 * explicitly opting back in.
 *
 * The down() method is intentionally a no-op: once caching is disabled we do
 * not restore the previous value on rollback, because we have no record of
 * what that value was.
 */
return new class() extends Migration {
	public function up(): void
	{
		DB::table('configs')
			->where('key', 'cache_enabled')
			->update(['value' => '0']);
	}

	public function down(): void
	{
		// Intentional no-op: previous value is not known.
	}
};
