<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('configs')->insert([
			[
				'key' => 'public_on_this_day',
				'value' => '0',
				'cat' => 'Smart Albums',
				'type_range' => '0|1',
				'confidentiality' => '0',
				'description' => 'Make "On This Day" smart album accessible to anonymous users',
			],
		]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', '=', 'public_on_this_day')->delete();
	}
};
