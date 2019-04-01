<?php

use App\Configs;
use App\Photo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddHidpi extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasColumn('photos', 'thumb2x')) {
			if (Schema::hasTable('configs')) {

				DB::table('configs')->insert([
					[
						'key'   => 'thumb_2x',
						'value' => '1'
					],
					[
						'key'   => 'small_2x',
						'value' => '0'
					],
					[
						'key'   => 'medium_2x',
						'value' => '0'
					],
				]);
			}
			else {
				echo "Table configs does not exists\n";
			}

			if (Schema::hasTable('photos')) {
				Schema::table('photos', function (Blueprint $table) {
					$table->renameColumn('medium', 'medium_old');
				});

				Schema::table('photos', function (Blueprint $table) {
					$table->renameColumn('small', 'small_old');
				});

				Schema::table('photos', function (Blueprint $table) {
					$table->char('medium', 20)->default('');
					$table->char('medium2x', 20)->default('');
					$table->char('small', 20)->default('');
					$table->char('small2x', 20)->default('');
					$table->boolean('thumb2x')->default(true);
				});
			}
		}

		if (Schema::hasTable('photos')) {
			$photos = Photo::all();
			foreach ($photos as $photo) {
				$save = false;

				// Verify that the 2x thumbnail actually exists. We assume
				// it does but we support the case where it does not.
				$thumbUrl2x = explode(".", $photo->thumbUrl);
				if (count($thumbUrl2x) < 2) {
					$photo->thumb2x = 0;
					$save = true;
				}
				else {
					$thumbUrl2x = $thumbUrl2x[0].'@2x.'.$thumbUrl2x[1];
					if (!file_exists(Config::get('defines.dirs.LYCHEE_UPLOADS_THUMB').$thumbUrl2x)) {
						$photo->thumb2x = 0;
						$save = true;
					}
				}

				// Extract the sizes of medium and small
				if ($photo->medium_old == '1') {
					if (file_exists(Config::get('defines.dirs.LYCHEE_UPLOADS_MEDIUM').$photo->url)) {
						list($width, $height) = getimagesize(Config::get('defines.dirs.LYCHEE_UPLOADS_MEDIUM').$photo->url);
						$photo->medium = $width.'x'.$height;
						$save = true;
					}
					else {
						echo "Missing file ".Config::get('defines.dirs.LYCHEE_UPLOADS_MEDIUM').$photo->url."\n";
						$this->failMessage();
					}
				}
				if ($photo->small_old == '1') {
					if (file_exists(Config::get('defines.dirs.LYCHEE_UPLOADS_SMALL').$photo->url)) {
						list($width, $height) = getimagesize(Config::get('defines.dirs.LYCHEE_UPLOADS_SMALL').$photo->url);
						$photo->small = $width.'x'.$height;
						$save = true;
					}
					else {
						echo "Missing file ".Config::get('defines.dirs.LYCHEE_UPLOADS_SMALL').$photo->url."\n";
						$this->failMessage();
					}
				}

				if ($save) {
					$photo->save();
				}
			}

			Schema::table('photos', function (Blueprint $table) {
				$table->dropColumn('medium_old', 'small_old');
			});
		}
		else {
			echo "Table photos does not exist\n";
		}
	}



	/**
     * Provide diagnostics to the caller
     *
     * @return void
     */
	private function failMessage()
	{
		$ignoreFile = Config::get('defines.dirs.LYCHEE_UPLOADS').'/ignore-missing-files.txt';
		if (!file_exists($ignoreFile)) {
			echo "Please ensure that photos are moved to the new installation and run this command again!\n\n";
			echo "To ignore, run this command again after creating a file at ".$ignoreFile."\n";
			echo "You can then create intermediate sizes later using 'php artisan generate_thumbs'\n";
			exit(1);
		}
	}



	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		if (env('DB_DROP_CLEAR_TABLES_ON_ROLLBACK', false)) {
			Configs::where('key', '=', 'thumb_2x')->delete();
			Configs::where('key', '=', 'small_2x')->delete();
			Configs::where('key', '=', 'medium_2x')->delete();

			Schema::table('photos', function (Blueprint $table) {
				$table->renameColumn('medium', 'medium_new');
			});

			Schema::table('photos', function (Blueprint $table) {
				$table->renameColumn('small', 'small_new');
			});

			Schema::table('photos', function (Blueprint $table) {
				$table->boolean('medium')->default(true);
				$table->boolean('small')->default(true);
			});

			$photos = Photo::all();
			foreach ($photos as $photo) {
				$save = false;

				if ($photo->medium_new === '') {
					$photo->medium = 0;
					$save = true;
				}
				if ($photo->small_new === '') {
					$photo->small = 0;
					$save = true;
				}

				if ($save) {
					$photo->save();
				}
			}

			Schema::table('photos', function (Blueprint $table) {
				$table->dropColumn('medium_new', 'medium2x', 'small_new', 'small2x', 'thumb2x');
			});
		}
	}
}
