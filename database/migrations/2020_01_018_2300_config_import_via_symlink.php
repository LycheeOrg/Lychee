<?php

/** @noinspection PhpUndefinedClassInspection */
use App\Configs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class ConfigImportViaSymlink extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (!defined('BOOL')) {
			define('BOOL', '0|1');
		}

		if (Schema::hasTable('configs')) {
			DB::table('configs')->insert([
				[
					'key' => 'import_via_symlink',
					'value' => 0,
					'confidentiality' => 2,
					'cat' => 'Image Processing',
					'type_range' => BOOL,
				],
			]);
		} else {
			echo "Table configs does not exists\n";
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
			Configs::where('key', '=', 'import_via_symlink')->delete();
		}
	}
}
