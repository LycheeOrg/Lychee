<?php

use App\Configs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCompressionQuality extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        //	    if(Schema::hasTable('configs')) {
//
//		    DB::table('configs')->insert([
//			    ['key' => 'compression_quality', 'value' => '90'],
//		    ]);
//	    }
//	    else {
//		    echo "Table configs does not exists\n";
//	    }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        //	    if(env('DB_DROP_CLEAR_TABLES_ON_ROLLBACK',false)) {
//	    	Configs::where('key','=','compression_quality')->delete();
//	    }
    }
}
