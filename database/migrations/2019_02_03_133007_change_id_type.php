<?php

use App\Album;
use App\Photo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeIdType extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// create copy to back up the data
		Schema::table('user_album', function (Blueprint $table) {
			$table->bigInteger('album_id_save')->unsigned()->nullable()->default(null)->after('album_id');
		});
		Schema::table('photos', function (Blueprint $table) {
			$table->bigInteger('album_id_save')->unsigned()->nullable()->default(null)->index()->after('album_id');
		});
		Schema::table('albums', function (Blueprint $table) {
			$table->bigInteger('parent_id_save')->unsigned()->nullable()->default(null)->after('parent_id');
		});

		// copy
		Photo::where('album_id_save', null)->update([
			'album_id_save' => DB::raw('album_id'),
		]);
		Album::where('parent_id_save', null)->update([
			'parent_id_save' => DB::raw('parent_id'),
		]);
		DB::table('user_album')->where('album_id_save', null)->update([
			'album_id_save' => DB::raw('album_id'),
		]);

		Schema::table('user_album', function (Blueprint $table) {
			if (DB::getDriverName() !== 'sqlite') {
				$table->dropForeign(['album_id']);
			}
			$table->dropColumn('album_id');
		});
		Schema::table('photos', function (Blueprint $table) {
			if (DB::getDriverName() !== 'sqlite') {
				$table->dropForeign(['album_id']);
			}
			$table->dropColumn('album_id');
		});
		Schema::table('albums', function (Blueprint $table) {
			if (DB::getDriverName() !== 'sqlite') {
				$table->dropForeign(['parent_id']);
			}
			$table->dropColumn('parent_id');
		});

		Schema::table('albums', function (Blueprint $table) {
			$table->bigIncrements('id')->change();
			$table->bigInteger('parent_id')->unsigned()->nullable()->default(null)->index()->after('parent_id_save');
		});
		Schema::table('albums', function (Blueprint $table) {
			$table->foreign('parent_id')->references('id')->on('albums');
		});

		Schema::table('user_album', function (Blueprint $table) {
			$table->bigInteger('album_id')->unsigned()->nullable()->default(null)->index()->after('album_id_save');
			$table->foreign('album_id')->references('id')->on('albums')->onDelete('cascade');
		});
		Schema::table('photos', function (Blueprint $table) {
			$table->bigInteger('album_id')->unsigned()->nullable()->default(null)->index()->after('album_id_save');
			$table->foreign('album_id')->references('id')->on('albums')->onDelete('cascade');
		});

		// copy
		Photo::where('album_id', null)->update([
			'album_id' => DB::raw('album_id_save'),
		]);
		Album::where('parent_id', null)->update([
			'parent_id' => DB::raw('parent_id_save'),
		]);
		DB::table('user_album')->where('album_id', null)->update([
			'album_id' => DB::raw('album_id_save'),
		]);

		Schema::table('photos', function (Blueprint $table) {
			$table->dropColumn(['album_id_save']);
		});
		Schema::table('user_album', function (Blueprint $table) {
			$table->dropColumn(['album_id_save']);
		});
		Schema::table('albums', function (Blueprint $table) {
			$table->dropColumn(['parent_id_save']);
		});

		Schema::table('photos', function (Blueprint $table) {
			$table->bigIncrements('id')->change();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		echo 'There is no going back for ' . __CLASS__ . "!\n";
	}
}
