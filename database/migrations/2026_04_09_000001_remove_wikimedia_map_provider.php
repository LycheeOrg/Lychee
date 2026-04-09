<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Remove the deprecated Wikimedia map provider option.
 *
 * Wikimedia discontinued their public tile service. Any installations
 * still configured to use it are migrated to OpenStreetMap.org instead.
 */
return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		// Migrate any existing Wikimedia setting to OpenStreetMap.org.
		DB::table('configs')
			->where('key', '=', 'map_provider')
			->where('value', '=', 'Wikimedia')
			->update(['value' => 'OpenStreetMap.org']);

		// Remove Wikimedia from the allowed type_range.
		DB::table('configs')
			->where('key', '=', 'map_provider')
			->update(['type_range' => 'map_provider']);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')
			->where('key', '=', 'map_provider')
			->update(['type_range' => 'map_provider']);
	}
};
