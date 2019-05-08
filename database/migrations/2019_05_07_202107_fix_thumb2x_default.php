<?php

use App\Photo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixThumb2xDefault extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('photos')) {

			$photos = Photo::all();
			foreach ($photos as $photo) {
				if ($photo->thumbUrl === '' && $photo->thumb2x === 1) {
					echo "Fixing thumb2x in ".$photo->title."\n";
					$photo->thumb2x = 0;
					$photo->save();
				}
			}
			Schema::table('photos', function (Blueprint $table) {
				$table->boolean('thumb2x')->default(false)->change();
			});
		}
		else {
			echo "Table photos does not exist\n";
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('photos', function (Blueprint $table) {
			$table->boolean('thumb2x')->default(true)->change();
		});
	}
}
