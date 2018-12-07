<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLicenseAlbum extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    Schema::table('albums', function (Blueprint $table) {
		    $table->char('license',20)->default('none')->after('password');
	    });
	    Schema::table('photos', function (Blueprint $table) {
		    $table->dropColumn(['license']);
	    });
	    Schema::table('photos', function (Blueprint $table) {
		    $table->char('license',20)->default('none')->after('small');
	    });
	    DB::table('configs')->insert([
		    ['key' => 'default_license', 'value' => 'none']
	    ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
	    Schema::table('albums', function (Blueprint $table) {
		    $table->dropColumn(['license']);
	    });
	    Schema::table('photos', function (Blueprint $table) {
		    $table->dropColumn(['license']);
	    });
	    Schema::table('photos', function (Blueprint $table) {
		    $table->char('license',20)->default('')->after('small');
	    });
	    DB::table('configs')->where('key','=','default_license')->delete();
    }
}
