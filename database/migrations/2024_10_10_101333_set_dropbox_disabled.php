<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('configs')
			->where('key', '=', 'dropbox_key')
			->where('value', '=', '')
			->update([
				'value' => 'disabled',
				'details' => 'Use value "disabled" to mark this setting as such.']);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')
			->where('key', '=', 'dropbox_key')
			->where('value', '=', 'disabled')
			->update([
				'value' => '',
				'details' => '']);
	}
};
