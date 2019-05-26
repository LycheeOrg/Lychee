<?php

use App\Configs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class RemovePlugins extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        //		Configs::where('key', '=', 'plugins')->delete();
    }

    /**
     * Reverse the migrations.
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
