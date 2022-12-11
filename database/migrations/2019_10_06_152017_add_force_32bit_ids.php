<?php

use App\Models\Configs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddForce32BitIds extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		defined('BOOL') or define('BOOL', '0|1');

		DB::table('configs')->insert([
			[
				'key' => 'force_32bit_ids',
				'value' => '0',
				'cat' => 'config',
				'type_range' => BOOL,
				'confidentiality' => '0',
			],
		]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Configs::where('key', '=', 'force_32bit_ids')->delete();
	}
}
