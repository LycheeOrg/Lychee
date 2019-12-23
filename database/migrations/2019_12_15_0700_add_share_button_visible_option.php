<?php

use App\Album;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShareButtonVisibleOption extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('albums') && !Schema::hasColumn('albums', 'share_button_visible')) {
			Schema::table('albums', function (Blueprint $table) {
				$table->boolean('share_button_visible')->after('downloadable')->default(false);
			});

			Album::where('id', '>', 1)
				->where('public', '=', 1)
				->update([
					'share_button_visible' => true,
				]);
		}

		if (!DB::table('configs')->where('key', 'share_button_visible')->exists()) {
			if (!defined('BOOL')) {
				define('BOOL', '0|1');
			}

			DB::table('configs')->insert([
				'key' => 'share_button_visible',
				'value' => '0',
				'cat' => 'config',
				'type_range' => BOOL,
				'confidentiality' => '0',
			]);
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		if (Schema::hasTable('albums')) {
			Schema::table('albums', function (Blueprint $table) {
				$table->dropColumn('share_button_visible');
			});
		}

		if (DB::table('configs')->where('key', 'share_button_visible')->exists()) {
			DB::table('configs')->where('key', 'share_button_visible')->delete();
		}
	}
}