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
		DB::table('configs')->insert([
			[
				'key' => 'album_subtitle_type',
				'value' => 'oldstyle',
				'confidentiality' => '0',
				'cat' => 'Gallery',
				'type_range' => 'description|takedate|creation|oldstyle',
			],
		]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::table('configs')->where('key', '=', 'album_subtitle_type')->delete();
	}
};
