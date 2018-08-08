<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;



//MariaDB [lychee]> show columns from lychee_photos;
//+-------------+---------------------+------+-----+---------+-------+
//| Field       | Type                | Null | Key | Default | Extra |
//+-------------+---------------------+------+-----+---------+-------+
//| id          | bigint(14) unsigned | NO   | PRI | NULL    |       |
//| title       | varchar(100)        | NO   |     |         |       |
//| description | varchar(1000)       | YES  |     |         |       |
//| url         | varchar(100)        | NO   |     | NULL    |       |
//| tags        | varchar(1000)       | NO   |     |         |       |
//| public      | tinyint(1)          | NO   |     | NULL    |       |
//| type        | varchar(10)         | NO   |     | NULL    |       |
//| width       | int(11)             | NO   |     | NULL    |       |
//| height      | int(11)             | NO   |     | NULL    |       |
//| size        | varchar(20)         | NO   |     | NULL    |       |
//| iso         | varchar(15)         | NO   |     | NULL    |       |
//| aperture    | varchar(20)         | NO   |     | NULL    |       |
//| make        | varchar(50)         | NO   |     | NULL    |       |
//| model       | varchar(50)         | NO   |     | NULL    |       |
//| shutter     | varchar(30)         | NO   |     | NULL    |       |
//| focal       | varchar(20)         | NO   |     | NULL    |       |
//| takestamp   | int(11)             | YES  |     | NULL    |       |
//| star        | tinyint(1)          | NO   | MUL | NULL    |       |
//| thumbUrl    | char(37)            | NO   |     | NULL    |       |
//| album       | bigint(14) unsigned | NO   | MUL | NULL    |       |
//| checksum    | char(40)            | YES  |     | NULL    |       |
//| medium      | tinyint(1)          | NO   |     | 0       |       |


class CreatePhotosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('photos');
        Schema::create('photos', function (Blueprint $table) {
            $table->Increments('id');
            $table->char('title',100);
            $table->text('description')->nullable();
            $table->char('url',100);
            $table->text('tags');
            $table->boolean('public');
            $table->char('type',10);
            $table->integer('width');
            $table->integer('height');
            $table->char('size',20);
            $table->char('iso',15);
            $table->char('aperture',20);
            $table->char('make',50);
            $table->char('model',50);
            $table->char('shutter',30);
            $table->char('focal',20);
            $table->integer('takestamp')->nullable();
            $table->boolean('star')->default(false);
            $table->char('thumbUrl',37);
            $table->integer('album_id')->unsigned()->nullable()->default(null);
            $table->foreign('album_id')->references('id')->on('albums')->onDelete('cascade');
            $table->char('checksum',40);
            $table->boolean('medium')->default(false);
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
        Schema::dropIfExists('photos');
    }
}
