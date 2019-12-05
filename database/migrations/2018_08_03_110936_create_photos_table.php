<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
		if (!Schema::hasTable('photos')) {
			//        Schema::dropIfExists('photos');
			Schema::create('photos', function (Blueprint $table) {
				$table->bigIncrements('id');
				$table->string('title', 100);
				$table->text('description')->nullable();
				$table->string('url', 100);
				$table->text('tags');
				$table->boolean('public');
				$table->integer('owner_id')->default(0);
				$table->string('type', 15)->default('');
				$table->integer('width')->nullable();
				$table->integer('height')->nullable();
				$table->string('size', 20)->default('');
				$table->string('iso', 15)->default('');
				$table->string('aperture', 20)->default('');
				$table->string('make', 50)->default('');
				$table->string('model', 50)->default('');
				$table->string('lens', 100)->default('');
				$table->string('shutter', 30)->default('');
				$table->string('focal', 20)->default('');
				$table->decimal('latitude', 10, 8)->nullable();
				$table->decimal('longitude', 11, 8)->nullable();
				$table->decimal('altitude', 10, 4)->nullable();
				$table->timestamp('takestamp')->nullable();
				$table->boolean('star')->default(false);
				$table->string('thumbUrl', 37)->default('');
				$table->bigInteger('album_id')->unsigned()->nullable()->default(null)->index();
				$table->foreign('album_id')->references('id')->on('albums')->onDelete('cascade');
				$table->string('checksum', 40)->default('');
				$table->boolean('medium')->default(false);
				$table->boolean('small')->default(false);
				$table->string('license', 20)->default('none');
				$table->timestamps();
			});
		} else {
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
		if (env('DB_DROP_CLEAR_TABLES_ON_ROLLBACK', false)) {
			Schema::dropIfExists('photos');
		}
	}
}
