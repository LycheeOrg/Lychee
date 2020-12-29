<?php

use App\Models\Album;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class NestedSetForAlbums extends Migration
{
	private const ALBUMS = 'albums';
	private const PHOTOS = 'photos';
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

		Album::fixTree();
		$update = resolve(UpdateTakestamps::class);

		/*
		 * Update all takestamps
		 * - we do it here and not in the lychee_photo migration anymore.
		 * - we could not do it yet then as the tree was not initialized.
		 */
		$update->all();
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
}
