<?php

use App\Album;
use App\Logs;
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
				$results = DB::table(env('DB_OLD_LYCHEE_PREFIX', '') . 'lychee_photos')->select('*')->orderBy('id', 'asc')->orderBy('album', 'asc')->get();
				$id = 0;
				$album_id = 0;
				foreach ($results as $result) {
					$photo = new Photo();
					$id = Helpers::trancateIf32($result->id, $id);
					if ($result->album == 0) {
						$album = null;
					} else {
						$album_id = Helpers::trancateIf32($result->album, $album_id);
						$album = $album_id;
					}
					$photo->id = $id;
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
					$thumbUrl2x = explode('.', $result->thumbUrl);
					if (count($thumbUrl2x) < 2) {
						$photo->thumb2x = 0;
					} else {
						$thumbUrl2x = $thumbUrl2x[0] . '@2x.' . $thumbUrl2x[1];
						if (!Storage::exists('thumb/' . $thumbUrl2x)) {
							$photo->thumb2x = 0;
						} else {
							$photo->thumb2x = 1;
						}
					}

					$photo->album_id = $album;
					$photo->checksum = $result->checksum;
					if (Storage::exists('medium/' . $photo->url)) {
						list($width, $height) = getimagesize(Storage::path('medium/' . $photo->url));
						$photo->medium = $width . 'x' . $height;
					} else {
						$photo->medium = '';
					}
					if (Storage::exists('medium/' . $photo->url)) {
						list($width, $height) = getimagesize(Storage::path('small/' . $photo->url));
						$result->small = $width . 'x' . $height;
					} else {
						$result->small = '';
					}
					$photo->license = $result->license;
					$photo->save();
				}
			} else {
				Logs::notice(__FUNCTION__, __LINE__, env('DB_OLD_LYCHEE_PREFIX', '') . 'lychee_photos does not exist!');
			}
		} else {
			Logs::notice(__FUNCTION__, __LINE__, 'photos is not empty.');
		}

		Album::reset_takestamp();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		if (Schema::hasTable('lychee_photos')) {
			Photo::truncate();
		}
	}
}
