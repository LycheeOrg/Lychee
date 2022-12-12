<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up(): void
	{
		// flip the locked value
		DB::table('users')->update(['is_locked' => DB::raw('NOT is_locked')]);

		// rename the column
		Schema::table('users', function (Blueprint $table) {
			$table->renameColumn('is_locked', 'may_edit_own_settings');
		});

		// create administration variable
		Schema::table('users', function (Blueprint $table) {
			$table->boolean('may_administrate')->after('email')->default(false);
		});
		DB::table('users')->where('id', '=', '0')->update(['may_administrate' => true]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down(): void
	{
		Schema::table('users', function (Blueprint $table) {
			$table->dropColumn('may_administrate');
		});

		// rename and flip.
		Schema::table('users', function (Blueprint $table) {
			$table->renameColumn('may_edit_own_settings', 'is_locked');
		});
		// flip the locked value
		DB::table('users')->update(['is_locked' => DB::raw('NOT is_locked')]);
	}
};
