<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddImgDirection extends Migration
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
				$table->decimal('imgDirection', 10, 4)->default(null)->after('altitude')->nullable();
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
				$table->dropColumn('imgDirection');
			});
		}
	}
}
