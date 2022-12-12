<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use function Safe\file_get_contents;
use function Safe\file_put_contents;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up(): void
	{
		defined('STRING') or define('STRING', 'string');

		$userCss = file_get_contents(public_path('dist/user.css'));

		DB::table('configs')->insert([
			[
				'key' => 'user_css',
				'value' => $userCss,
				'confidentiality' => 2,
				'cat' => 'Gallery',
				'type_range' => STRING,
				'description' => 'Enable Smart Albums',
			],
		]);

		// This is not a function of `Safe\` because if the dir is not writable we just ignore it.
		unlink(public_path('dist/user.css'));
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down(): void
	{
		$value = DB::table('configs')->where('key', '=', 'user_css')->first()->value;
		touch(public_path('dist/user.css'));
		file_put_contents(public_path('dist/user.css'), $value);
		DB::table('configs')->where('key', '=', 'user_css')->delete();
	}
};
