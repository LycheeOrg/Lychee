<?php

use Illuminate\Database\Migrations\Migration;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up(): void
	{
		DB::table('configs')->insert([
			[
				'key' => 'allow_username_change',
				'value' => '1',
				'confidentiality' => 0,
				'cat' => 'config',
				'type_range' => '0|1',
				'description' => 'Allow users to change their username.',
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
			->where('key', '=', 'allow_username_change')
			->delete();
	}
};
