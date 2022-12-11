<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	private const ALBUM = 'albums';
	private const SMART_COLUMN_NAME = 'smart';
	private const SHOWTAGS_COLUMN_NAME = 'showtags';

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table(self::ALBUM, function ($table) {
			$table->boolean(self::SMART_COLUMN_NAME)->default(false)->after('license');
		});
		Schema::table(self::ALBUM, function ($table) {
			$table->text(self::SHOWTAGS_COLUMN_NAME)->after(self::SMART_COLUMN_NAME)->nullable();
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
			$table->dropColumn(self::SMART_COLUMN_NAME);
		});
		Schema::table(self::ALBUM, function (Blueprint $table) {
			$table->dropColumn(self::SHOWTAGS_COLUMN_NAME);
		});
	}
};