<?php

use App\Facades\Helpers;
use App\Models\Logs;
use App\Models\Photo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

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
				foreach ($results as $result) {
					$photoAttributes = [];
					$id = Helpers::trancateIf32($result->id, $id);
					$photoAttributes['id'] = $id;
					if ($result->album == 0) {
						$photoAttributes['album_id'] = null;
					} else {
						$photoAttributes['album_id'] = Helpers::trancateIf32($result->album, 0);
					}
					$photoAttributes['title'] = $result->title;
					$photoAttributes['description'] = $result->description;
					$photoAttributes['url'] = $result->url;
					$photoAttributes['tags'] = $result->tags;
					$photoAttributes['public'] = $result->public;
					$photoAttributes['type'] = $result->type;
					$photoAttributes['width'] = $result->width;
					$photoAttributes['height'] = $result->height;
					$photoAttributes['size'] = $result->size;
					$photoAttributes['iso'] = $result->iso;
					$photoAttributes['aperture'] = $result->aperture;
					$photoAttributes['make'] = $result->make;
					$photoAttributes['lens'] = $result->lens ?? '';
					$photoAttributes['model'] = $result->model;
					$photoAttributes['shutter'] = $result->shutter;
					$photoAttributes['focal'] = $result->focal;
					$photoAttributes['takestamp'] = ($result->takestamp == 0 || $result->takestamp == null) ? null : date('Y-m-d H:i:s', $result->takestamp);
					$photoAttributes['star'] = $result->star;
					$photoAttributes['thumbUrl'] = $result->thumbUrl;
					$thumbUrl2x = explode('.', $result->thumbUrl);
					if (count($thumbUrl2x) < 2) {
						$photoAttributes['thumb2x'] = 0;
					} else {
						$thumbUrl2x = $thumbUrl2x[0] . '@2x.' . $thumbUrl2x[1];
						if (!Storage::exists('thumb/' . $thumbUrl2x)) {
							$photoAttributes['thumb2x'] = 0;
						} else {
							$photoAttributes['thumb2x'] = 1;
						}
					}
					$photoAttributes['checksum'] = $result->checksum;
					if (Storage::exists('medium/' . $photoAttributes['url'])) {
						list($width, $height) = getimagesize(Storage::path('medium/' . $photoAttributes['url']));
						$photoAttributes['medium'] = $width . 'x' . $height;
					} else {
						$photoAttributes['medium'] = '';
					}
					if (Storage::exists('small/' . $photoAttributes['url'])) {
						list($width, $height) = getimagesize(Storage::path('small/' . $photoAttributes['url']));
						$result->small = $width . 'x' . $height;
					} else {
						$result->small = '';
					}
					$photoAttributes['license'] = $result->license ?? 'none';

					$photoModel = new Photo();
					$photoModel->setRawAttributes($photoAttributes);
					$photoModel->save();
				}
			} else {
				Logs::notice(__FUNCTION__, __LINE__, env('DB_OLD_LYCHEE_PREFIX', '') . 'lychee_photos does not exist!');
			}
		} else {
			Logs::notice(__FUNCTION__, __LINE__, 'photos is not empty.');
		}
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
