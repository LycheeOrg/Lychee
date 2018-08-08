<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


//MariaDB [lychee]> show columns from lychee_albums;
//+--------------+---------------------+------+-----+---------+-------+
//| Field        | Type                | Null | Key | Default | Extra |
//+--------------+---------------------+------+-----+---------+-------+
//| id           | bigint(14) unsigned | NO   | PRI | NULL    |       |
//| title        | varchar(100)        | NO   |     |         |       |
//| description  | varchar(1000)       | YES  |     |         |       |
//| sysstamp     | int(11)             | NO   |     | NULL    |       |
//| public       | tinyint(1)          | NO   |     | 0       |       |
//| visible      | tinyint(1)          | NO   |     | 1       |       |
//| downloadable | tinyint(1)          | NO   |     | 0       |       |
//| password     | varchar(100)        | YES  |     | NULL    |       |
//+--------------+---------------------+------+-----+---------+-------+

class CreateAlbumsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('albums');
        Schema::create('albums', function (Blueprint $table) {
            $table->increments('id');
            $table->char('title',100)->default('');
            $table->text('description');
//            $table->integer('sysstamp')->default(0);
            $table->boolean('public')->default(false);
            $table->boolean('visible_hidden')->default(true);
            $table->boolean('downloadable')->default(false);
            $table->char('password',100)->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('albums');
    }
}
