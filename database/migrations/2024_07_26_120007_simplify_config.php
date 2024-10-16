<?php

use Illuminate\Database\Migrations\Migration;

return new class() extends Migration {
	protected const BOOL = '0|1';
	protected const BOOL_STRING = 'bool';

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up(): void
	{
		DB::table('configs')->where('cat', '=', self::BOOL_STRING)->update(['cat' => self::BOOL]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		// Nothing to do
	}
};
