<?php

use App\Configs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddModFrame extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
//	    if(Schema::hasTable('configs')) {
//
//		    DB::table('configs')->insert([
//			    ['key' => 'Mod_Frame', 'value' => '0'],
//			    ['key' => 'Mod_Frame_refresh', 'value' => '30000'],
//		    ]);
//	    }
//	    else {
//		    echo "Table configs does not exists\n";
//	    }
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
//	    if (env('DB_DROP_CLEAR_TABLES_ON_ROLLBACK', false)) {
//		    Configs::where('key', '=', 'Mod_Frame')->delete();
//		    Configs::where('key', '=', 'Mod_Frame_refresh')->delete();
//	    }
	}
}
