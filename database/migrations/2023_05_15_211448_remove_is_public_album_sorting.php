<?php

use Illuminate\Database\Migrations\Migration;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('configs')->where('key', '=', 'sorting_albums_col')->where('value', '=', 'is_public')->update(['value' => 'max_taken_at']);
		DB::table('configs')->where('key', '=', 'sorting_albums_col')->update(['type_range' => 'created_at|title|description|max_taken_at|min_taken_at']);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', '=', 'sorting_albums_col')->update(['type_range' => 'created_at|title|description|is_public|max_taken_at|min_taken_at']);
	}
};
