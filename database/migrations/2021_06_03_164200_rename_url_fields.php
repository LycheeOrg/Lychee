<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameUrlFields extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('photos', function (Blueprint $table) {
			$table->renameColumn('url', 'filename');
		});
		Schema::table('photos', function (Blueprint $table) {
			$table->renameColumn('thumbUrl', 'thumb_filename');
		});
		Schema::table('photos', function (Blueprint $table) {
			$table->renameColumn('livePhotoUrl', 'live_photo_filename');
		});
		Schema::table('photos', function (Blueprint $table) {
			$table->renameColumn('livePhotoContentID', 'live_photo_content_id');
		});
		Schema::table('photos', function (Blueprint $table) {
			$table->renameColumn('livePhotoChecksum', 'live_photo_checksum');
		});
		Schema::table('photos', function (Blueprint $table) {
			$table->integer('width')->nullable(false)->change();
		});
		Schema::table('photos', function (Blueprint $table) {
			$table->integer('height')->nullable(false)->change();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('photos', function (Blueprint $table) {
			$table->renameColumn('filename', 'url');
		});
		Schema::table('photos', function (Blueprint $table) {
			$table->renameColumn('thumb_filename', 'thumbUrl');
		});
		Schema::table('photos', function (Blueprint $table) {
			$table->renameColumn('live_photo_filename', 'livePhotoUrl');
		});
		Schema::table('photos', function (Blueprint $table) {
			$table->renameColumn('live_photo_content_id', 'livePhotoContentID');
		});
		Schema::table('photos', function (Blueprint $table) {
			$table->renameColumn('live_photo_checksum', 'livePhotoChecksum');
		});
	}
}
