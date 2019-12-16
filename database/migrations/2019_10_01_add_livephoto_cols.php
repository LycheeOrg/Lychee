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
		if (Schema::hasTable('photos')) {
			Schema::table('photos', function ($table) {
				$table->string('livePhotoUrl')->default(null)->after('thumbURL')->nullable();
			});

			Schema::table('photos', function ($table) {
				$table->string('livePhotoContentID')->default(null)->after('thumb2x')->nullable();
			});

			Schema::table('photos', function ($table) {
				$table->string('livePhotoChecksum', 40)->default(null)->after('checksum')->nullable();
			});
		} else {
			echo "Table photos does not exists\n";
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		if (Schema::hasTable('photos')) {
			Schema::table('photos', function (Blueprint $table) {
				$table->dropColumn('livePhotoContentID');
				$table->dropColumn('livePhotoUrl');
				$table->dropColumn('livePhotoChecksum');
			});
		}
	}
}
