<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLens extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    Schema::table('photos', function (Blueprint $table) {
		    $table->char('lens',100)->default('')->after('model');
	    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
	    Schema::table('photos', function (Blueprint $table) {
		    $table->dropColumn(['lens']);
	    });
    }
}
