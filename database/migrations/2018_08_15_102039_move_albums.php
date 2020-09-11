<?php

use App\Assets\Helpers;
use App\Models\Album;
use App\Models\Logs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MoveAlbums extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (count(Album::all()) == 0) {
			if (Schema::hasTable(env('DB_OLD_LYCHEE_PREFIX', '') . 'lychee_albums')) {
				$results = DB::table(env('DB_OLD_LYCHEE_PREFIX', '') . 'lychee_albums')->select('*')->orderBy('id', 'asc')->get();
				$id = 0;
				foreach ($results as $result) {
					$album = new Album();
					$id = Helpers::trancateIf32($result->id, $id);
					$album->id = $id;
					$album->title = $result->title;
					$album->description = $result->description;
					$album->public = $result->public;
					$album->visible_hidden = $result->visible;
					$album->license = $result->license ?? 'none';
					$album->created_at = date('Y-m-d H:i:s', $result->sysstamp);
					$album->save();
				}
			} else {
				Logs::notice(__FUNCTION__, __LINE__, env('DB_OLD_LYCHEE_PREFIX', '') . 'lychee_albums does not exist!');
			}
		} else {
			Logs::notice(__FUNCTION__, __LINE__, 'albums is not empty.');
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		if (Schema::hasTable('lychee_albums')) {
			Schema::disableForeignKeyConstraints();
			Album::truncate();
			Schema::enableForeignKeyConstraints();
		}
	}
}
