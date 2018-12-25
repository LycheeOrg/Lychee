<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MediumSize extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    DB::table('configs')->insert([
		    ['key' => 'small_max_width', 'value' => '0'],
		    ['key' => 'small_max_height', 'value' => '360'],
		    ['key' => 'medium_max_width', 'value' => '1920'],
		    ['key' => 'medium_max_height', 'value' => '1080'],
	    ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
	    DB::table('configs')->where('key','=','small_max_width')->delete();
	    DB::table('configs')->where('key','=','medium_max_width')->delete();
	    DB::table('configs')->where('key','=','small_max_height')->delete();
	    DB::table('configs')->where('key','=','medium_max_height')->delete();
    }
}
