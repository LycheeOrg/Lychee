<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddZipOptions extends Migration
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
				'key' => 'zip_deflate_level',
				'value' => '6',
				'confidentiality' => 0,
				'cat' => 'config',
				'type_range' => '-1|0|1|2|3|4|5|6|7|8|9',
				'description' => 'DEFLATE compression level: -1 = disable compression (use STORE method), 0 = no compression (use DEFLATE method), 1 = minimal compression (fast), ... 9 = maximum compression (slow)',
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
			->where('key', '=', 'zip_deflate_level')
			->delete();
	}
}
