<?php

use Illuminate\Database\Migrations\Migration;

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
			'key' => 'auto_fix_orientation',
			'value' => '1',
			'cat' => 'Image Processing',
			'type_range' => BOOL,
			'confidentiality' => '0',
			'description' => 'Automatically rotate imported images',
		]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::table('configs')->where('key', '=', 'auto_fix_orientation')->delete();
	}
};
