<?php

/** @noinspection PhpUndefinedClassInspection */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Fix32Bit extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (PHP_INT_MAX > 2147483647) {
			return;
		}

		// We need to chop off four least significant digits from the ids to
		// make them fit within a 32-bit integer.

		Schema::disableForeignKeyConstraints();

		// Start with the ids of 'albums' sincne they also affect the
		// album_ids in 'photos'.
		$albums = DB::table('albums')->get();
		$prevShortId = 0;
		foreach ($albums as $album) {
			// Chop off the last four digits.
			$shortId = intval(substr($album->id, 0, -4));
			if ($shortId <= $prevShortId) {
				$shortId = $prevShortId + 1;
			}
			DB::table('albums')->where('id', '=', $album->id)->update([
				'id' => $shortId,
			]);
			DB::table('photos')->where('album_id', '=', $album->id)->update([
				'album_id' => $shortId,
			]);
			$prevShortId = $shortId;
		}

		$photos = DB::table('photos')->get();
		$prevShortId = 0;
		foreach ($photos as $photo) {
			// Chop off the last four digits.
			$shortId = intval(substr($photo->id, 0, -4));
			if ($shortId <= $prevShortId) {
				$shortId = $prevShortId + 1;
			}
			DB::table('photos')->where('id', '=', $photo->id)->update([
				'id' => $shortId,
			]);
			$prevShortId = $shortId;
		}

		Schema::enableForeignKeyConstraints();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		if (PHP_INT_MAX > 2147483647) {
			return;
		}

		Schema::disableForeignKeyConstraints();

		// The chopped off digits are lost but we can always add some zeros.

		$albums = DB::table('albums')->get();
		foreach ($albums as $album) {
			DB::table('albums')->where('id', '=', $album->id)->update([
				'id' => $album->id . '0000',
			]);
		}

		$photos = DB::table('photos')->get();
		foreach ($photos as $photo) {
			DB::table('photos')->where('id', '=', $photo->id)->update([
				'id' => $photo->id . '0000',
				'album_id' => $photo->album_id . '0000',
			]);
		}

		Schema::enableForeignKeyConstraints();
	}
}
