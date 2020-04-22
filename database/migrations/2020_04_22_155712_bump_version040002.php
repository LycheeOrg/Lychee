<?php

use App\Configs;
use Illuminate\Support\Facades\Schema;

class BumpVersion040002 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Configs::where('key', 'version')->update(['value' => '040002']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		Configs::where('key', 'version')->update(['value' => '040002']);
    }
}
