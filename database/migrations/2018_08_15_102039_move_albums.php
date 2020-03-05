<?php

use App\Album;
use App\Logs;
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
		$output = new \Symfony\Component\Console\Output\ConsoleOutput(2);

		if (count(Album::all()) == 0) {
			if (Schema::hasTable(env('DB_OLD_LYCHEE_PREFIX', '') . 'lychee_albums')) {
				$results = DB::table(env('DB_OLD_LYCHEE_PREFIX', '') . 'lychee_albums')->select('*')->get();
				foreach ($results as $result) {
					$album = new Album();
					$album->id = $result->id;
					$album->title = $result->title;
					$album->description = $result->description;
					$album->public = $result->public;
					$album->visible_hidden = $result->visible;
					$album->license = $result->license;
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
		if (env('DB_DROP_CLEAR_TABLES_ON_ROLLBACK', false)) {
			if (Schema::hasTable('lychee_albums')) {
				DB::table('albums')->delete();
			}
		}
	}
}
