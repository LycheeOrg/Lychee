<?php

use App\Configs;
use Illuminate\Database\Migrations\Migration;

class RenameJustifiedLayout extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // rename
        Configs::where('key', 'justified_layout')->update([
			    'key' => 'layout',
	    ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // rename
        Configs::where('key', 'layout')->update([
			    'key' => 'justified_layout',
	    ]);
    }
}
