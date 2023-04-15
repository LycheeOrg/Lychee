<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	private const ALBUM = 'albums';
	private const SORT_COLUMN_NAME = 'sorting_col';
	private const SORT_COLUMN_ORDER = 'sorting_order';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table(self::ALBUM, function ($table) {
			$table->string(self::SORT_COLUMN_NAME, 30)->default('')->after('license');
		});
		Schema::table(self::ALBUM, function ($table) {
			$table->string(self::SORT_COLUMN_ORDER, 10)->default('DESC')->after(self::SORT_COLUMN_NAME);
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table(self::ALBUM, function (Blueprint $table) {
			$table->dropColumn(self::SORT_COLUMN_NAME);
		});
		Schema::table(self::ALBUM, function (Blueprint $table) {
			$table->dropColumn(self::SORT_COLUMN_ORDER);
		});
	}
};
