<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		defined('BOOL') or define('BOOL', '0|1');

		Schema::table('albums', function (Blueprint $table) {
			$table->boolean('share_button_visible')->after('downloadable')->default(false);
		});

		DB::table('albums')
			->where('public', '=', 1)
			->update([
				'share_button_visible' => true,
			]);

		DB::table('configs')->insert([
			'key' => 'share_button_visible',
			'value' => '0',
			'cat' => 'config',
			'type_range' => BOOL,
			'confidentiality' => '0',
		]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropColumns('albums', ['share_button_visible']);
		DB::table('configs')->where('key', 'share_button_visible')->delete();
	}
};
