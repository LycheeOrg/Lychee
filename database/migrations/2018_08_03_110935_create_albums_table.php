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
	    if(!Schema::hasTable('albums')) {
//        Schema::dropIfExists('albums');
		    Schema::create('albums', function (Blueprint $table) {
			    $table->bigIncrements('id');
			    $table->char('title', 100)->default('');
			    $table->integer('owner_id')->default(0);
			    $table->bigInteger('parent_id')->unsigned()->nullable();
			    $table->foreign('parent_id')->references('id')->on('albums');
			    $table->text('description');
			    $table->timestamp('min_takestamp')->nullable();
			    $table->timestamp('max_takestamp')->nullable();
			    $table->boolean('public')->default(false);
			    $table->boolean('visible_hidden')->default(true);
			    $table->boolean('downloadable')->default(false);
			    $table->char('password', 100)->nullable()->default(null);
			    $table->char('license', 20)->default('none');
			    $table->timestamps();
		    });
	    }
	    else {
		    echo "Table albums already exists\n";
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
		    Schema::dropIfExists('albums');
	    }
    }
}
