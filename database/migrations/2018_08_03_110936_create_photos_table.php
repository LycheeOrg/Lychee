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
	    if(!Schema::hasTable('photos')) {
//        Schema::dropIfExists('photos');
		    Schema::create('photos', function (Blueprint $table) {
			    $table->Increments('id');
			    $table->char('title', 100);
			    $table->text('description')->nullable();
			    $table->char('url', 100);
			    $table->text('tags');
			    $table->boolean('public');
			    $table->integer('owner_id')->default(0);
			    $table->char('type', 15)->default('');
			    $table->integer('width')->nullable();
			    $table->integer('height')->nullable();
			    $table->char('size', 20)->default('');
			    $table->char('iso', 15)->default('');
			    $table->char('aperture', 20)->default('');
			    $table->char('make', 50)->default('');
			    $table->char('model', 50)->default('');
			    $table->char('lens', 100)->default('');
			    $table->char('shutter', 30)->default('');
			    $table->char('focal', 20)->default('');
			    $table->decimal('latitude', 10, 8)->nullable();
			    $table->decimal('longitude', 11, 8)->nullable();
			    $table->decimal('altitude', 10, 4)->nullable();
			    $table->timestamp('takestamp')->nullable();
			    $table->boolean('star')->default(false);
			    $table->char('thumbUrl', 37)->default('');
			    $table->integer('album_id')->unsigned()->nullable()->default(null);
			    $table->foreign('album_id')->references('id')->on('albums')->onDelete('cascade');
			    $table->char('checksum', 40)->default('');
			    $table->boolean('medium')->default(false);
			    $table->boolean('small')->default(false);
			    $table->char('license', 20)->default('none');
			    $table->timestamps();
		    });
	    }
	    else {
		    echo "Table photos already exists\n";
	    }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
	    if(env('DB_DROP_CLEAR_TABLES_ON_ROLLBACK',false)) {
		    Schema::dropIfExists('photos');
	    }
    }
}
