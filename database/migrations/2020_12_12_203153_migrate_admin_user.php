<?php

use App\Exceptions\ModelDBException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 *
	 * @throws ModelDBException
	 */
	public function up(): void
	{
		$username = DB::table('configs')->select('value')->where('key', 'username')->first();
		$password = DB::table('configs')->select('value')->where('key', 'password')->first();

		DB::table('users')->updateOrInsert(['id' => 0],
			['username' => $username?->value ?? '',
				'password' => $password?->value ?? '', ]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 *
	 * @throws InvalidArgumentException
	 */
	public function down(): void
	{
		if (Schema::hasTable('users')) {
			DB::table('users')
				->where('id', '=', 0)
				->delete();
		}
	}
};
