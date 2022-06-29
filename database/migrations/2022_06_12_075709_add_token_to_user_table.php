<?php

use App\Models\Configs;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddTokenToUserTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function up(): void
	{
		$key_length = 16;

		Configs::where('key', '=', 'api_key')->delete();

		Schema::table('users', function (Blueprint $table) {
			$table->char('token', 100)->unique()->after('email')->default('needs-to-be-set');
		});

		foreach (User::all() as $user) {
			$user->token = strtr(base64_encode(random_bytes($key_length)), '+/', '-_');
			$user->save();
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down(): void
	{
		DB::table('configs')->insert([
			[
				'key' => 'api_key',
				'value' => User::query()->findOrFail(0)->token,
				'confidentiality' => 3,
				'cat' => 'Admin',
			],
		]);

		Schema::table('users', function (Blueprint $table) {
			$table->dropColumn('token');
		});
	}
}
