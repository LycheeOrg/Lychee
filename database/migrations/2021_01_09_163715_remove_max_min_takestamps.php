<?php

use App\Models\Album;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveMaxMinTakestamps extends Migration
{
	private const ALBUMS = 'albums';
	private const MIN = 'min_takestamp';
	private const MAX = 'max_takestamp';

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table(self::ALBUMS, function (Blueprint $table) {
			$table->dropColumn(self::MIN);
		});
		Schema::table(self::ALBUMS, function (Blueprint $table) {
			$table->dropColumn(self::MAX);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table(self::ALBUMS, function ($table) {
			$table->timestamp(self::MIN)->nullable()->after('description');
		});
		Schema::table(self::ALBUMS, function ($table) {
			$table->timestamp(self::MAX)->nullable()->after(self::MIN);
		});

		$albums = Album::query()->withoutGlobalScopes()->get();
		foreach ($albums as $_album) {
			$_album->min_takestamp = $_album->get_all_photos()->whereNotNull('takestamp')->min('takestamp');
			$_album->max_takestamp = $_album->get_all_photos()->whereNotNull('takestamp')->max('takestamp');
			$_album->save();
		}
	}
}
