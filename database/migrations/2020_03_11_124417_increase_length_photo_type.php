<?php

use App\Models\Logs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('photos', function (Blueprint $table) {
			$table->string('type', 30)->change();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Logs::warning(__FUNCTION__, __LINE__, 'There is no going back for ' . __CLASS__ . '!');
	}
};
