<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		// Convert existing integer values to string values
		DB::table('configs')
			->where('key', 'default_album_protection')
			->where('value', '1')
			->update(['value' => 'private']);

		DB::table('configs')
			->where('key', 'default_album_protection')
			->where('value', '2')
			->update(['value' => 'public']);

		DB::table('configs')
			->where('key', 'default_album_protection')
			->where('value', '3')
			->update(['value' => 'inherit']);

		DB::table('configs')
			->where('key', 'default_album_protection')
			->where('value', '4')
			->update(['value' => 'public_hidden']);

		// Update type_range and details to reflect new string values
		DB::table('configs')
			->where('key', 'default_album_protection')
			->update([
				'type_range' => 'private|public|inherit|public_hidden',
				'details' => 'private = album is only visible to owner, public = album is publicly visible, inherit = inherit from parent album, public_hidden = public but not listed',
			]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		// Convert string values back to integer values
		DB::table('configs')
			->where('key', 'default_album_protection')
			->where('value', 'private')
			->update(['value' => '1']);

		DB::table('configs')
			->where('key', 'default_album_protection')
			->where('value', 'public')
			->update(['value' => '2']);

		DB::table('configs')
			->where('key', 'default_album_protection')
			->where('value', 'inherit')
			->update(['value' => '3']);

		DB::table('configs')
			->where('key', 'default_album_protection')
			->where('value', 'public_hidden')
			->update(['value' => '4']);

		// Restore original type_range and clear details
		DB::table('configs')
			->where('key', 'default_album_protection')
			->update([
				'type_range' => '1|2|3|4',
				'details' => '',
			]);
	}
};
