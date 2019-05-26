<?php

use App\Configs;
use Illuminate\Database\Migrations\Migration;

class ApiKeySecurityLevel extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        $config = Configs::where('key', 'api_key')->first();
        $config->confidentiality = 3;
        $config->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        /*
         * This is a not reversible change.
         * Anyway the key would be deleted with the next rollback: add_api_key
         */
    }
}
