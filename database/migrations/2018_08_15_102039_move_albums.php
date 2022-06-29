<?php

use App\Facades\Helpers;
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
		if (DB::table('albums')->count('id') === 0) {
			if (Schema::hasTable(env('DB_OLD_LYCHEE_PREFIX', '') . 'lychee_albums')) {
				$results = DB::table(env('DB_OLD_LYCHEE_PREFIX', '') . 'lychee_albums')->select('*')->orderBy('id', 'asc')->get();
				$id = 0;
				foreach ($results as $result) {
					$id = Helpers::trancateIf32($result->id, $id);
					DB::table('albums')->insert([
						'id' => $id,
						'title' => $result->title,
						'description' => $result->description,
						'public' => $result->public,
						'visible_hidden' => $result->visible,
						'password' => $result->password,
						'license' => $result->license ?? 'none',
						'created_at' => date('Y-m-d H:i:s', $result->sysstamp),
					]);
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
