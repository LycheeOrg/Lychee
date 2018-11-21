<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSmall extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    Schema::table('photos', function (Blueprint $table) {
		    $table->boolean('small')->default(false)->after('medium');
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
		    $table->dropColumn(['small']);
	    });
    }
}
