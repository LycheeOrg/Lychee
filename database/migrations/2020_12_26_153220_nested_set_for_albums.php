<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Kalnoy\Nestedset\NodeTrait;

class NestedSetForAlbums extends Migration
{
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
}

/**
 * Model class specific for this migration.
 *
 * Migrations are required to be also runnable in the future after the code
 * base will have evolved.
 * To this end, migrations must not rely on a specific implementation of
 * models, because these models may change in the future, but the migration
 * must conduct its task with respect to a table layout which was valid at
 * the time when the migration was written.
 * In conclusion, this implies that migration should not use models but use
 * low-level DB queries when necessary.
 * Unfortunately, we need the `fixTree()` algorithm and there is no
 * implementation which uses low-level DB queries.
 */
class NestedSetForAlbums_AlbumModel extends Model
{
	use NodeTrait;

	protected $table = 'albums';
}
