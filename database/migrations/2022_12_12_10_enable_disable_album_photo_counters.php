@@ -0,0 +1,36 @@
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
			[
				'key' => 'show_num_albums',
				'value' => '0',
				'confidentiality' => 0,
				'cat' => 'Gallery',
				'type_range' => BOOL,
				'description' => 'Show subalbum count on album cover',
			],
			[
				'key' => 'show_num_photos',
				'value' => '0',
				'confidentiality' => 0,
				'cat' => 'Gallery',
				'type_range' => BOOL,
				'description' => 'Show number of photos on album cover badge',
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
		DB::table('configs')->whereIn('key', ['show_num_albums', 'show_num_photos'])->delete();
	}
};
