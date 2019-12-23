<?php

use Illuminate\Database\Migrations\Migration;

class GenerateInstalledLog extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$dateStamp = date('Y/m/d h:i:sa');
		$message = 'Lychee INSTALLED on ' . $dateStamp;
		file_put_contents(base_path('installed.log'), $message);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		// THIS WILL MAKE YOUR
		echo 'We do not delete ' . base_path('installed.log') . " as this would leave your installation vulnerable.\n";
	}
}
