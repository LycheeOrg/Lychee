<?php

use App\Configs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeleteImportedSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    if(Schema::hasTable('configs')) {

		    DB::table('configs')->insert([
			    ['key' => 'deleteImported', 'value' => '1'],
		    ]);
	    }
	    else {
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
	    if(env('DB_DROP_CLEAR_TABLES_ON_ROLLBACK',false)) {
		    Configs::where('key','=','deleteImported')->delete();
	    }
    }
}
