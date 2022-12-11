<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

require_once 'TemporaryModels/NestedSetForAlbums_AlbumModel.php';

return new class() extends Migration {
	private const ALBUMS = 'albums';
	private const LEFT = '_lft';
	private const RIGHT = '_rgt';

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table(self::ALBUMS, function ($table) {
			$table->unsignedBigInteger(self::LEFT)->nullable()->default(null)->after('parent_id');
		});
		Schema::table(self::ALBUMS, function ($table) {
			$table->unsignedBigInteger(self::RIGHT)->nullable()->default(null)->after(self::LEFT);
		});
		Schema::table(self::ALBUMS, function ($table) {
			$table->index([self::LEFT, self::RIGHT]);
		});

		NestedSetForAlbums_AlbumModel::query()->fixTree();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table(self::ALBUMS, function (Blueprint $table) {
			$table->dropColumn(self::LEFT);
		});
		Schema::table(self::ALBUMS, function (Blueprint $table) {
			$table->dropColumn(self::RIGHT);
		});
	}
};

