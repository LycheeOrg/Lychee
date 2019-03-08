<?php

use App\Configs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class PrivateConfigFields extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('configs')) {
			Schema::table('configs', function ($table) {
				$table->tinyInteger('confidentiality')->after('cat')->default(0);
			});

			// set confidentiality to 3 for those (never returned)
			Configs::where('key', 'username')
				->orWhere('key', 'password')
				->update([
					"confidentiality" => 4
				]);

			// require admin
			Configs::where('key', 'dropboxKey')
				->update([
					"confidentiality" => 3
				]);

			// normal users
			Configs::where('key', 'lang_available')
				->orWhere('key', 'imagick')
				->orWhere('key', 'compression_quality')
				->orWhere('key', 'skipDuplicates')
				->orWhere('key', 'sortingAlbums')
				->orWhere('key', 'sortingAlbums_col')
				->orWhere('key', 'sortingAlbums_order')
				->orWhere('key', 'sortingPhotos')
				->orWhere('key', 'sortingPhotos_col')
				->orWhere('key', 'sortingPhotos_order')
				->orWhere('key', 'default_license')
				->orWhere('key', 'thumb_2x')
				->orWhere('key', 'small_max_width')
				->orWhere('key', 'small_max_height')
				->orWhere('key', 'small_2x')
				->orWhere('key', 'medium_max_width')
				->orWhere('key', 'medium_max_height')
				->orWhere('key', 'medium_2x')
				->orWhere('key', 'landing_title')
				->orWhere('key', 'landing_background')
				->orWhere('key', 'landing_facebook')
				->orWhere('key', 'landing_flickr')
				->orWhere('key', 'landing_twitter')
				->orWhere('key', 'landing_youtube')
				->orWhere('key', 'landing_instagram')
				->orWhere('key', 'landing_owner')
				->orWhere('key', 'landing_subtitle')
				->orWhere('key', 'site_copyright_enable')
				->orWhere('key', 'site_copyright_begin')
				->orWhere('key', 'site_copyright_end')
				->orWhere('key', 'deleteImported')
				->update([
					"confidentiality" => 2
				]);

		}
		else {
			echo "Table configs does not exists\n";
		}
	}



	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		if (Schema::hasTable('configs')) {
			Schema::table('configs', function ($table) {
				$table->dropColumn('confidentiality');
			});
		}
	}
}
