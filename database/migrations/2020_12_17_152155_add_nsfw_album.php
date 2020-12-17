<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNsfwAlbum extends Migration
{
	private const ALBUM = 'albums';
	private const NSFW_COLUMN_NAME = 'nsfw';
	private const VIEWABLE = 'viewable';
	private const VISIBLE_HIDDEN = 'visible_hidden';

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table(self::ALBUM, function ($table) {
			$table->boolean(self::NSFW_COLUMN_NAME)->default(false)->after(self::VISIBLE_HIDDEN);
		});
		Schema::table(self::ALBUM, function (Blueprint $table) {
			$table->renameColumn(self::VISIBLE_HIDDEN, self::VIEWABLE);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table(self::ALBUM, function (Blueprint $table) {
			$table->dropColumn(self::NSFW_COLUMN_NAME);
		});
		Schema::table(self::ALBUM, function (Blueprint $table) {
			$table->renameColumn(self::VIEWABLE, self::VISIBLE_HIDDEN);
		});
	}
}
