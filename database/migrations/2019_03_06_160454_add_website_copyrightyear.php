<?php

/** @noinspection PhpUndefinedClassInspection */
use App\Configs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddWebsiteCopyrightyear extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (Schema::hasTable('configs')) {
            DB::table('configs')->insert([
			    ['key' => 'site_copyright_enable', 'value' => '1'],
			    ['key' => 'site_copyright_begin', 'value' => '2019'],
			    ['key' => 'site_copyright_end', 'value' => '2019'],
		    ]);
        } else {
            echo "Table configs does not exists\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (env('DB_DROP_CLEAR_TABLES_ON_ROLLBACK', false)) {
            Configs::where('key', '=', 'site_copyright_enable')->delete();
            Configs::where('key', '=', 'site_copyright_begin')->delete();
            Configs::where('key', '=', 'site_copyright_end')->delete();
        }
    }
}
