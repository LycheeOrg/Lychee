<?php

use App\Photo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MovePhotos extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// only do if photos is empty
		if (count(Photo::all()) == 0) {
			// check if there is a table to import from
			if (Schema::hasTable(env('DB_OLD_LYCHEE_PREFIX', '') . 'lychee_photos')) {
				$results = DB::table(env('DB_OLD_LYCHEE_PREFIX', '') . 'lychee_photos')->select('*')->get();
				foreach ($results as $result) {
					$photo = new Photo();
					$photo->id = $result->id;
					$photo->title = $result->title;
					$photo->description = $result->description;
					$photo->url = $result->url;
					$photo->tags = $result->tags;
					$photo->public = $result->public;
					$photo->type = $result->type;
					$photo->width = $result->width;
					$photo->height = $result->height;
					$photo->size = $result->size;
					$photo->iso = $result->iso;
					$photo->aperture = $result->aperture;
					$photo->make = $result->make;
					$photo->lens = $result->lens;
					$photo->model = $result->model;
					$photo->shutter = $result->shutter;
					$photo->focal = $result->focal;
					$photo->takestamp = ($result->takestamp == 0 || $result->takestamp == null) ? null : date('Y-m-d H:i:s', $result->takestamp);
					$photo->star = $result->star;
					$photo->thumbUrl = $result->thumbUrl;
					$photo->album_id = ($result->album == 0) ? null : $result->album;
					$photo->checksum = $result->checksum;
					$photo->medium = $result->medium;
					$photo->small = $result->small;
					$photo->license = $result->license;
					$photo->save();
				}
			} else {
				echo env('DB_OLD_LYCHEE_PREFIX', '') . "lychee_photos does not exist!\n";
			}
		} else {
			echo "photos is not empty.\n";
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
			if (Schema::hasTable('lychee_photos')) {
				DB::table('photos')->delete();
			}
		}
	}
}
