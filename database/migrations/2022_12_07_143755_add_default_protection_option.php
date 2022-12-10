<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddDefaultProtectionOption extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up(): void
	{
		DB::table('configs')->insert([
			[
				'key' => 'default_album_protection',
				'value' => '1',
				'confidentiality' => 0,
				'cat' => 'config',
				'type_range' => '1|2|3',
				'description' => 'Default protection for newly created albums. 1 = private, 2 = public, 3 = inherit from parent',
			],
		]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 *
	 * @throws InvalidArgumentException
	 */
	public function down(): void
	{
		DB::table('configs')
			->where('key', '=', 'default_album_protection')
			->delete();
	}
}
