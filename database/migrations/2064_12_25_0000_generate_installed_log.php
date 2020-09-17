<?php

use App\Models\Logs;
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
		$dateStamp = date('Y-m-d H:i:s');
		$message = 'Lychee INSTALLED before ' . $dateStamp;
		@file_put_contents(base_path('installed.log'), $message);
		Logs::warning(__METHOD__, __LINE__, 'Installation completed.');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		// If we reverted the above change, it would make the laravel installation vulnerable.
		// A malicious user could access ./install.php and read the content of .env !!
		Logs::warning(__METHOD__, __LINE__, 'We do not delete ' . base_path('installed.log') . ' as this would leave your installation vulnerable.');
	}
}
