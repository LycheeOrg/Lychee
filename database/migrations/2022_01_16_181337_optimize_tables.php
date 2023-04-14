<?php

use Illuminate\Database\Migrations\Migration;

require_once 'TemporaryModels/OptimizeTables.php';

return new class() extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$optimize = new OptimizeTables();
		$optimize->exec();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		// Nothing do to here.
	}
};
