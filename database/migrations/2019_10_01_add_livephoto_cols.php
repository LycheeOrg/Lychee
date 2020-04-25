<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LivephotoCols extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('photos', function ($table) {
			$table->string('livePhotoUrl')->default(null)->after('thumbURL')->nullable();
		});

		Schema::table('photos', function ($table) {
			$table->string('livePhotoContentID')->default(null)->after('thumb2x')->nullable();
		});

		Schema::table('photos', function ($table) {
			$table->string('livePhotoChecksum', 40)->default(null)->after('checksum')->nullable();
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
			$table->dropColumn('livePhotoContentID');
		});
		Schema::table('photos', function (Blueprint $table) {
			$table->dropColumn('livePhotoUrl');
		});
		Schema::table('photos', function (Blueprint $table) {
			$table->dropColumn('livePhotoChecksum');
		});
	}
}
