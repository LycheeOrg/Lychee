<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		defined('BOOL') or define('BOOL', '0|1');

		DB::table('configs')->insert([
			'key' => 'use_last_modified_date_when_no_exif_date',
			'value' => '0',
			'cat' => 'Image Processing',
			'type_range' => BOOL,
			'confidentiality' => '0',
			'description' => 'Use the file\'s last modified time when Exif data has no creation date',
		]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::table('configs')->where('key', '=', 'use_last_modified_date_when_no_exif_date')->delete();
	}
};
