<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RenameCapabilities extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::beginTransaction();

		// flip the locked value
		$ids = DB::table('users')->where('is_locked', '=', true)->get('id')->pluck('id');
		DB::table('users')->whereIn('id', $ids)->update(['is_locked' => 0]);
		DB::table('users')->whereNotIn('id', $ids)->update(['is_locked' => 1]);

		// rename the column
		Schema::table('users', function (Blueprint $table) {
			$table->renameColumn('is_locked', 'may_edit_own_settings');
		});

		// create administration variable
		Schema::table('users', function (Blueprint $table) {
			$table->boolean('may_administrate')->after('email')->default(false);
		});
		DB::table('users')->where('id', '=', '0')->update(['may_administrate' => true]);

		DB::commit();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::beginTransaction();

		Schema::table('users', function (Blueprint $table) {
			$table->dropColumn('may_administrate');
		});

		// rename and flip.
		Schema::table('users', function (Blueprint $table) {
			$table->renameColumn('may_edit_own_settings', 'is_locked');
		});
		$ids = DB::table('users')->where('is_locked', '=', true)->get('id')->pluck('id');
		DB::table('users')->whereIn('id', $ids)->update(['is_locked' => 0]);
		DB::table('users')->whereNotIn('id', $ids)->update(['is_locked' => 1]);

		DB::commit();
	}
}
