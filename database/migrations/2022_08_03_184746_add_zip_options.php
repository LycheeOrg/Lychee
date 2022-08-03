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
				'key' => 'zip_large_file_size',
				'value' => '30000000',
				'confidentiality' => 0,
				'cat' => 'config',
				'type_range' => 'int',
				'description' => 'Threshold in bytes above which files are not compressed but simply stored',
			], [
				'key' => 'zip_deflate_level',
				'value' => '6',
				'confidentiality' => 0,
				'cat' => 'config',
				'type_range' => '-1|0|1|2|3|4|5|6|7|8|9',
				'description' => 'DEFLATE compression level: -1 = disable compression, 0 = no compression, 1 = minimal compression, ... 9 = maximum compression',
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
			->whereIn('key', ['zip_large_file_size', 'zip_deflate_level'])
			->delete();
	}
}
