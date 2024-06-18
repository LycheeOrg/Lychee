<?php

declare(strict_types=1);

/** @noinspection PhpUndefinedClassInspection */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('configs')->where('key', 'SL_enable')->update(['confidentiality' => '2']);
		DB::table('configs')->where('key', 'SL_for_admin')->update(['confidentiality' => '2']);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', 'SL_enable')->update(['confidentiality' => '0']);
		DB::table('configs')->where('key', 'SL_for_admin')->update(['confidentiality' => '0']);
	}
};
