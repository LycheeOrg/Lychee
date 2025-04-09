<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	private const COL_VISIT = 'visit_count';
	private const COL_DOWNLOAD = 'download_count';
	private const COL_FAVOURITE = 'favourite_count';
	private const COL_SHARED = 'shared_count';

	private const TABLE_PHOTOS = 'photos';
	private const TABLE_ALBUMS = 'base_albums';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table(self::TABLE_PHOTOS, function (Blueprint $table): void {
			$table->unsignedBigInteger(self::COL_VISIT)->default(0)->after('live_photo_checksum')->comment('Number of times this photo has been viewed');
			$table->unsignedBigInteger(self::COL_DOWNLOAD)->default(0)->after(self::COL_VISIT)->comment('Number of times this photo has been downloaded (excluding albums)');
			$table->unsignedBigInteger(self::COL_FAVOURITE)->default(0)->after(self::COL_DOWNLOAD)->comment('Number of times this photo has been favourite');
			$table->unsignedBigInteger(self::COL_SHARED)->default(0)->after(self::COL_FAVOURITE)->comment('Number of times this photo has been shared');
		});

		Schema::table(self::TABLE_ALBUMS, function (Blueprint $table): void {
			$table->unsignedBigInteger(self::COL_VISIT)->default(0)->after('copyright')->comment('Number of times this album has been viewed');
			$table->unsignedBigInteger(self::COL_DOWNLOAD)->default(0)->after(self::COL_VISIT)->comment('Number of times this album has been downloaded');
			$table->unsignedBigInteger(self::COL_SHARED)->default(0)->after(self::COL_DOWNLOAD)->comment('Number of times this album has been shared');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table(self::TABLE_PHOTOS, function (Blueprint $table) { $table->dropColumn(self::COL_VISIT); });
		Schema::table(self::TABLE_PHOTOS, function (Blueprint $table) { $table->dropColumn(self::COL_FAVOURITE); });
		Schema::table(self::TABLE_PHOTOS, function (Blueprint $table) { $table->dropColumn(self::COL_DOWNLOAD); });
		Schema::table(self::TABLE_PHOTOS, function (Blueprint $table) { $table->dropColumn(self::COL_SHARED); });

		Schema::table(self::TABLE_ALBUMS, function (Blueprint $table) { $table->dropColumn(self::COL_VISIT); });
		Schema::table(self::TABLE_ALBUMS, function (Blueprint $table) { $table->dropColumn(self::COL_DOWNLOAD); });
		Schema::table(self::TABLE_ALBUMS, function (Blueprint $table) { $table->dropColumn(self::COL_SHARED); });
	}
};
