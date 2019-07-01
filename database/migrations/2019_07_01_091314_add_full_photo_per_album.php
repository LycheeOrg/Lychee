<?php

use App\Album;
use App\Configs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFullPhotoPerAlbum extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('albums')) {
			Schema::table('albums', function (Blueprint $table) {
				$table->boolean('full_photo')->after('public')->default(true);
			});

			Album::where('id', '>', 1)
				->update([
					'full_photo' => intval(Configs::get_value('full_photo', 1)),
				]);
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		if (Schema::hasTable('albums')) {
			Schema::table('albums', function (Blueprint $table) {
				$table->dropColumn('full_photo');
			});
		}
	}
}
