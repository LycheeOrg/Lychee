<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
MariaDB [lychee]> show columns from lychee_settings;
+-------+--------------+------+-----+---------+-------+
| Field | Type         | Null | Key | Default | Extra |
+-------+--------------+------+-----+---------+-------+
| key   | varchar(50)  | NO   |     |         |       |
| value | varchar(200) | YES  |     |         |       |
+-------+--------------+------+-----+---------+-------+
*/

class CreateConfigsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('configs')) {
			//        Schema::dropIfExists('configs');
			Schema::create('configs', function (Blueprint $table) {
				$table->increments('id');
				$table->string('key', 50);
				$table->string('value', 200)->nullable();
			});

			DB::table('configs')->insert([
				['key' => 'version', 'value' => '040000'],
				['key' => 'username', 'value' => ''],
				['key' => 'password', 'value' => ''],
				['key' => 'checkForUpdates', 'value' => '0'],
				['key' => 'sortingPhotos_col', 'value' => 'takestamp'],
				['key' => 'sortingPhotos_order', 'value' => 'ASC'],
				['key' => 'sortingAlbums_col', 'value' => 'max_takestamp'],
				['key' => 'sortingAlbums_order', 'value' => 'ASC'],
				['key' => 'imagick', 'value' => '1'],
				['key' => 'dropboxKey', 'value' => ''],
				['key' => 'skipDuplicates', 'value' => '0'],
				['key' => 'small_max_width', 'value' => '0'],
				['key' => 'small_max_height', 'value' => '360'],
				['key' => 'medium_max_width', 'value' => '1920'],
				['key' => 'medium_max_height', 'value' => '1080'],
				['key' => 'lang', 'value' => 'en'],
				['key' => 'layout', 'value' => '1'],
				['key' => 'image_overlay', 'value' => '1'],
				['key' => 'image_overlay_type', 'value' => 'desc'],
				['key' => 'default_license', 'value' => 'none'],
				['key' => 'compression_quality', 'value' => '90'],
				['key' => 'full_photo', 'value' => '1'],
				['key' => 'deleteImported', 'value' => '0'],

				['key' => 'Mod_Frame', 'value' => '1'],
				['key' => 'Mod_Frame_refresh', 'value' => '30000'],
			]);
		} else {
			echo "Table configs already exists\n";
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		if (env('DB_DROP_CLEAR_TABLES_ON_ROLLBACK', false)) {
			Schema::dropIfExists('configs');
		}
	}
}
