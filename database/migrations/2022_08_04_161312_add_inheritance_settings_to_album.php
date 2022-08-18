<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInheritanceSettingsToAlbum extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		defined('BOOL') or define('BOOL', '0|1');

		Schema::table('base_albums', function (Blueprint $table) {
			$table->boolean('inherits_protection_policy')->after('is_share_button_visible')->nullable(false)->default(false);
		});

		DB::table('configs')->insert([
			[
				'key' => 'default_inherits_protection_policy',
				'value' => '0',
				'confidentiality' => 0,
				'cat' => 'Gallery',
				'description' => 'default setting for visibility inheritance',
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
		Schema::table('base_albums', function (Blueprint $table) {
			$table->dropColumn('inherits_protection_policy');
		});

		DB::table('configs')->where('key', '=', 'default_inherits_protection_policy')->delete();
	}
}
