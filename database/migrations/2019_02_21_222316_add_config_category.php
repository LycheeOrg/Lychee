<?php
/** @noinspection PhpUndefinedClassInspection */

use App\Configs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddConfigCategory extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('configs')) {
			Schema::table('configs', function ($table) {
				$table->string('cat', 50)->after('value')->default('Config');
			});
			Configs::where('key','like','Mod_Frame%')->update([
				'cat' => 'Mod Frame'
			]);
			Configs::where('key','like','landing_%')->update([
				'cat' => 'Landing Page'
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
		if (Schema::hasTable('configs')) {
			Schema::table('configs', function ($table) {
				$table->dropColumn('cat');
			});
		}
	}
}
