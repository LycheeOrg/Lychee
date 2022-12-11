<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTrackShortPathToAlbumTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('albums', function (Blueprint $table) {
			$table->string('track_short_path')->after('cover_id')->nullable()->default(null);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('albums', function (Blueprint $table) {
			$table->dropColumn('track_short_path');
		});
	}
}
