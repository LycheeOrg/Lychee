<?php

use App\Models\Configs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ConfigMarkdown extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		defined('BOOL') or define('BOOL', '0|1');

		DB::table('configs')->insert([
			[
				'key' => 'display_album_description',
				'value' => '1',
				'confidentiality' => 0,
				'cat' => 'Gallery',
				'type_range' => BOOL,
			],
			[
				'key' => 'markdown_in_descriptions',
				'value' => '0',
				'confidentiality' => 0,
				'cat' => 'Gallery',
				'type_range' => BOOL,
			],
		]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Configs::where('key', '=', 'display_album_description')->delete();
		Configs::where('key', '=', 'markdown_in_descriptions')->delete();
	}
}
