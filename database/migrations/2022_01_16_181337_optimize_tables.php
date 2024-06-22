<?php

use Illuminate\Database\Migrations\Migration;

require_once 'TemporaryModels/OptimizeTables.php';

return new class() extends Migration {
	private OptimizeTables $optimize;

	public function __construct()
	{
		$this->optimize = new OptimizeTables();
	}

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		$this->optimize->exec();
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		// Nothing do to here.
	}
};
