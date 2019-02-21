<?php

use App\Configs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class RemovePlugins extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
//		Configs::where('key', '=', 'plugins')->delete();
	}



	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
//		if (Schema::hasTable('configs')) {
//			DB::table('configs')->insert([
//				['key'   => 'plugins',
//				 'value' => ''
//				],
//			]);
//		}
//		else {
//			echo "Table configs does not exists\n";
//		}
	}
}
