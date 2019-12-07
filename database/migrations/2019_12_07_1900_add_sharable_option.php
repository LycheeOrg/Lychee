<?php

use App\Album;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSharableOption extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('albums') && !Schema::hasColumn('albums', 'sharable')) {
			Schema::table('albums', function (Blueprint $table) {
				$table->boolean('sharable')->after('downloadable')->default(false);
			});

			Album::where('id', '>', 1)
				->where('public', '=', 1)
				->update([
					'sharable' => true,
				]);
		}

		define('BOOL', '0|1');
		if (!DB::table('configs')->where('key', 'sharable')->exists()) {
			DB::table('configs')->insert([
				'key' => 'sharable',
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
				$table->dropColumn('sharable');
			});
		}

		if (DB::table('configs')->where('key', 'sharable')->exists()) {
			DB::table('configs')->where('key', 'sharable')->delete();
		}
	}
}