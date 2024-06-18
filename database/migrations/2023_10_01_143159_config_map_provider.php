<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('configs')->where('key', '=', 'map_provider')->update(['type_range' => 'map_provider']);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', '=', 'map_provider')->update(['type_range' => 'Wikimedia|OpenStreetMap.org|OpenStreetMap.de|OpenStreetMap.fr|RRZE']);
	}
};
