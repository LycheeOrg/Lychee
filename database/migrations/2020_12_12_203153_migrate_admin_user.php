<?php

use App\Exceptions\ModelDBException;
use App\Models\Configs;
use App\Models\User;
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
		$user = User::query()->findOrNew(0);
		$user->incrementing = false; // disable auto-generation of ID
		$user->id = 0;
		Configs::invalidateCache();
		$user->username = Configs::getValueAsString('username', '');
		$user->password = Configs::getValueAsString('password', '');
		$user->save();
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
