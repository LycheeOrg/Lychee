<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToXHProfTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (DB::connection()->getDriverName() !== 'mysql') {
			return;
		}

		Schema::table('details', function (Blueprint $table) {
			$table->dropColumn('idcount');
			$table->char('id', 64)->primary()->change();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		if (DB::connection()->getDriverName() !== 'mysql') {
			return;
		}

		Schema::table('details', function (Blueprint $table) {
			$table->dropPrimary();
		});

		Schema::table('details', function (Blueprint $table) {
			$table->id('idcount');
			$table->char('id', 64)->change();
		});
	}
}
