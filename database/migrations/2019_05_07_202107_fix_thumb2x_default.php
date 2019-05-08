<?php
/** @noinspection PhpUndefinedClassInspection */

use App\Photo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixThumb2xDefault extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('photos')) {


			Photo::where('thumbUrl', '=', '')
				->where('thumb2x', '=', '1')
				->update([
					'thumb2x' => 0
				]);

			Schema::table('photos', function (Blueprint $table) {
				$table->boolean('thumb2x')->default(false)->change();
			});
		}
		else {
			echo "Table photos does not exist\n";
		}
	}



	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('photos', function (Blueprint $table) {
			$table->boolean('thumb2x')->default(true)->change();
		});
	}
}
