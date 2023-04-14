<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @throws InvalidArgumentException
	 */
	public function up(): void
	{
		$value = DB::table('configs')
			->where('key', '=', 'Mod_Frame_refresh')
			->value('value');
		if (is_numeric($value)) {
			DB::table('configs')
				->where('key', '=', 'Mod_Frame_refresh')
				->update(['value' => strval(intval(floatval($value) / 1000.0))]);
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @throws InvalidArgumentException
	 */
	public function down(): void
	{
		$value = DB::table('configs')
			->where('key', '=', 'Mod_Frame_refresh')
			->value('value');
		if (is_numeric($value)) {
			DB::table('configs')
				->where('key', '=', 'Mod_Frame_refresh')
				->update(['value' => strval(intval(floatval($value) * 1000.0))]);
		}
	}
};
