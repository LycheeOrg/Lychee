<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/*
MariaDB [lychee]> show columns from lychee_settings;
+-------+--------------+------+-----+---------+-------+
| Field | Type         | Null | Key | Default | Extra |
+-------+--------------+------+-----+---------+-------+
| key   | varchar(50)  | NO   |     |         |       |
| value | varchar(200) | YES  |     |         |       |
+-------+--------------+------+-----+---------+-------+
*/

class CreateConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('configs');
        Schema::create('configs', function (Blueprint $table) {
            $table->increments('id');
            $table->char('key',50);
            $table->char('value',200)->nullable();
        });

        DB::table('configs')->insert([
            ['key' => 'version', 'value' => '030102'],
            ['key' => 'username', 'value' => ''],
            ['key' =>  'password', 'value' => ''],
            ['key' =>  'checkForUpdates', 'value' => '1'],
            ['key' =>  'sortingPhotos_col', 'value' => 'takestamp'],
            ['key' =>  'sortingPhotos_order', 'value' => 'ASC'],
            ['key' =>  'sortingAlbums_col', 'value' => 'description'],
            ['key' =>  'sortingAlbums_order', 'value' => 'DESC'],
            ['key' =>  'imagick', 'value' => '1'],
            ['key' =>  'dropboxKey', 'value' => ''],
            ['key' =>  'identifier', 'value' => 'a74587f1cb706a9f4ea1691a4771027e'], // this is to be decided by you.
            ['key' =>  'skipDuplicates', 'value' => '0'],
            ['key' =>  'plugins', 'value' => ''],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('configs');
    }
}
