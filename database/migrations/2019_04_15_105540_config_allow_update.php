<?php

/** @noinspection PhpUndefinedClassInspection */
use App\Configs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class ConfigAllowUpdate extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('configs')) {
			DB::table('configs')->insert([
				[
					'key' => 'allow_online_git_pull',
					'value' => '0',
					'confidentiality' => 3,
				],
				[
					'key' => 'force_migration_in_production',
					'value' => '0',
					'confidentiality' => 3,
				],
			]);
		} else {
			echo "Table configs does not exist\n";
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		if (env('DB_DROP_CLEAR_TABLES_ON_ROLLBACK', false)) {
			Configs::where('key', '=', 'allow_online_git_pull')->delete();
			Configs::where('key', '=', 'force_migration_in_production')->delete();
		}
	}
}
