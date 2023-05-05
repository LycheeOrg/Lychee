<?php

use Illuminate\Database\Migrations\Migration;

require_once 'TemporaryModels/OptimizeTables.php';

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		$optimize = new OptimizeTables();
		$optimize->exec();
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		// Nothing do to here.
	}
};
