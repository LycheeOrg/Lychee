<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	public const TABLE_SV = 'size_variants';
	public const COLUMN = 'storage_disk';
	public const DEFAULT = 'images';

	// ! remove me in final PR
	public const TABLE_PHOTO = 'photos';
	public const COLUMN_REMOVED = 'external_storage';

	public function up(): void
	{
		// ! remove me in final PR
		if (Schema::hasColumn(self::TABLE_SV, self::COLUMN_REMOVED)) {
			Schema::table(self::TABLE_SV, function (Blueprint $table) {
				$table->dropColumn(self::COLUMN_REMOVED);
			});
		}
		// ! remove me in final PR
		Schema::disableForeignKeyConstraints();
		if (Schema::hasColumn(self::TABLE_PHOTO, self::COLUMN_REMOVED)) {
			Schema::table(self::TABLE_PHOTO, function (Blueprint $table) {
				$table->dropColumn(self::COLUMN_REMOVED);
			});
		}
		Schema::enableForeignKeyConstraints();

		Schema::table(self::TABLE_SV, function (Blueprint $table) {
			$table->string(self::COLUMN)->default(self::DEFAULT);
		});
	}

	public function down(): void
	{
		Schema::table(self::TABLE_SV, function (Blueprint $table) {
			$table->dropColumn(self::COLUMN);
		});
	}
};
