<?php

use App\Configs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWebsiteTitle extends Migration
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
			    ['key' => 'site_title', 'value' => 'Lychee v4'],
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
	    if (env('DB_DROP_CLEAR_TABLES_ON_ROLLBACK', false)) {
		    Configs::where('key', '=', 'site_title')->delete();
	    }
    }
}
