<?php

use App\Configs;
use Illuminate\Database\Migrations\Migration;

class RenamePhpScriptLimit extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Configs::where('key', 'php_script_limit')->update([
			'key' => 'php_script_no_limit',
		]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Configs::where('key', 'php_script_no_limit')->update([
			'key' => 'php_script_limit',
		]);
	}
}
