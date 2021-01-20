<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAlbumCover extends Migration
{
	private const ALBUMS = 'albums';
	private const COVER = 'cover_id';

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table(self::ALBUMS, function (Blueprint $table) {
			$table->bigInteger(self::COVER)->unsigned()->nullable()->default(null);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table(self::ALBUMS, function (Blueprint $table) {
			$table->dropColumn(self::COVER);
		});
	}
}
