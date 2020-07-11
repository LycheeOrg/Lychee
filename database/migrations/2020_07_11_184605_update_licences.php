<?php

use App\Photo;
use Illuminate\Database\Migrations\Migration;

class UpdateLicences extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// Get all CC licences
		$photos = Photo::where('license', 'like', 'CC-%')->get();
		if (count($photos) == 0) {
			return false;
		}
		foreach ($photos as $photo) {
			$photo->license = $photo->license . '-4.0';
			$photo->save();
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		// Get all CC licences
		$photos = Photo::where('license', 'like', 'CC-%')->get();
		if (count($photos) == 0) {
			return false;
		}
		foreach ($photos as $photo) {
			// Delete version
			$photo->license = substr($photo->license, 0, -4);
			$photo->save();
		}
	}
}
