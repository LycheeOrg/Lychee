<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//MariaDB [lychee]> show columns from lychee_log;
//+----------+--------------+------+-----+---------+----------------+
//| Field    | Type         | Null | Key | Default | Extra          |
//+----------+--------------+------+-----+---------+----------------+
//| id       | int(11)      | NO   | PRI | NULL    | auto_increment |
//| time     | int(11)      | NO   |     | NULL    |                |
//| type     | varchar(11)  | NO   |     | NULL    |                |
//| function | varchar(100) | NO   |     | NULL    |                |
//| line     | int(11)      | NO   |     | NULL    |                |
//| text     | text         | YES  |     | NULL    |                |
//+----------+--------------+------+-----+---------+----------------+

class CreateLogsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('logs')) {
			//        Schema::dropIfExists('logs');
			Schema::create('logs', function (Blueprint $table) {
				$table->bigIncrements('id');
				$table->string('type', 11);
				$table->string('function', 100);
				$table->integer('line');
				$table->text('text');
				$table->timestamps();
			});
		} else {
			echo "Table logs already exists\n";
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
			Schema::dropIfExists('logs');
		}
	}
}
