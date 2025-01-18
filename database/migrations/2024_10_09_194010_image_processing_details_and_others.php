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
		DB::table('configs')->where('key', 'auto_fix_orientation')->update(['details' => '<span class="pi pi-exclamation-triangle text-orange-500"></span> Original images will be overwritten and compressed.']);
		DB::table('configs')->whereIn('key', [
			'date_format_photo_thumb',
			'date_format_photo_overlay',
			'date_format_sidebar_uploaded',
			'date_format_sidebar_taken_at',
			'date_format_hero_min_max',
			'date_format_hero_created_at',
			'date_format_album_thumb',
		])->update(['details' => 'See <a class="underline" href="https://www.php.net/manual/en/datetime.format.php">datetime.format.php</a>']);
		DB::table('configs')->where('key', 'license_key')->update(['details' => 'Get Supporter Edition here: <a class="underline" href="https://lycheeorg.dev/get-supporter-edition">https://lycheeorg.dev/get-supporter-edition</a>']);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', 'auto_fix_orientation')->update(['details' => '']);
		DB::table('configs')->whereIn('key', [
			'date_format_photo_thumb',
			'date_format_photo_overlay',
			'date_format_sidebar_uploaded',
			'date_format_sidebar_taken_at',
			'date_format_hero_min_max',
			'date_format_hero_created_at',
			'date_format_album_thumb',
		])->update(['details' => 'See https://www.php.net/manual/en/datetime.format.php']);
	}
};
