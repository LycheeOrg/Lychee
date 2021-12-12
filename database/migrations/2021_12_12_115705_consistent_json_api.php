<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ConsistentJsonApi extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// SQLite does not support renaming more than one column in a single
		// schema modification,
		Schema::table('users', function (Blueprint $table) {
			$table->renameColumn('upload', 'may_upload');
		});
		Schema::table('users', function (Blueprint $table) {
			$table->renameColumn('lock', 'is_locked');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		// SQLite does not support renaming more than one column in a single
		// schema modification,
		Schema::table('users', function (Blueprint $table) {
			$table->renameColumn('may_upload', 'upload');
		});
		Schema::table('users', function (Blueprint $table) {
			$table->renameColumn('is_locked', 'lock');
		});
	}
}
