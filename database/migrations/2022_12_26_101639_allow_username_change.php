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
	 * @throws InvalidArgumentException
	 */
	public function down(): void
	{
		DB::table('configs')
			->where('key', '=', 'allow_username_change')
			->delete();
	}
};
