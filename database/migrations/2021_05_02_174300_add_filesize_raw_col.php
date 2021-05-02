<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFileSizeRawCol extends Migration
{
	private const TABLE_NAME = 'photos';
	private const COLUMN_NAME = 'filesize_raw';

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table(self::TABLE_NAME, function (Blueprint $table) {
			$table->unsignedInteger(self::COLUMN_NAME)->default(0)->after('size');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table(self::TABLE_NAME, function (Blueprint $table) {
			$table->dropColumn(self::COLUMN_NAME);
		});
	}
}
