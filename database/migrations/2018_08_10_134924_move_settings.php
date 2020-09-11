<?php

use App\Models\Configs;
use App\Models\Logs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MoveSettings extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// this test is to make sure this is not executed when we passed a certain migration point
		if (Schema::hasTable(env('DB_OLD_LYCHEE_PREFIX', '') . 'lychee_settings')) {
			if (Configs::where('key', '=', 'check_for_updates')->count() == 0) {
				$results = DB::table(env('DB_OLD_LYCHEE_PREFIX', '') . 'lychee_settings')->select('*')->orderBy('key', 'asc')->get();

				foreach ($results as $result) {
					/*
					+---------------------+--------------------------------------------------------------+
					| key                 | value                                                        |
					+---------------------+--------------------------------------------------------------+
					| checkForUpdates     | 1                                                            |
					| default_license     | none                                                         |
					| deleteImported      | 1                                                            |
					| dropboxKey          |                                                              |
					| full_photo          | 1                                                            |
					| hide_version_number | 1                                                            |
					| identifier          | b99833cc9f9464d11cc4bc7ea9ba79bd                             |
					| image_overlay       | 0                                                            |
					| image_overlay_type  | exif                                                         |
					| imagick             | 1                                                            |
					| lang                | en                                                           |
					| layout              | 0                                                            |
					| medium_max_height   | 1080                                                         |
					| medium_max_width    | 1920                                                         |
					| password            | $2y$10$AOPUp58lXH6bOSJ.WR9DkujeetgNYhTdzxBSfPZN.Knybyvl9QJ8K |
					| php_script_limit    | 0                                                            |
					| plugins             |                                                              |
					| public_search       | 0                                                            |
					| skipDuplicates      | 0                                                            |
					| small_max_height    | 360                                                          |
					| small_max_width     | 0                                                            |
					| sortingAlbums       | ORDER BY id DESC                                             |
					| sortingPhotos       | ORDER BY id DESC                                             |
					| useExiftool         | 0                                                            |
					| username            | $2y$10$iHySVP/2qyZ3jfoV/DhZcuLQc1qhkDax929HVLPeAS9AtakK09lXu |
					| version             | update_030216                                                |
					+---------------------+--------------------------------------------------------------+
					*/
					if (in_array($result->key, ['sortingAlbums', 'sortingPhotos'])) {
						$order_by = explode(' ', $result->value);
						Configs::where('key', '=', $result->key . '_col')->update(['value' => $order_by[2] ?? 'id']);
						Configs::where('key', '=', $result->key . '_order')->update(['value' => $order_by[3] ?? 'DESC']);
					} elseif (!in_array($result->key, ['checkForUpdates', 'hide_version_number', 'identifier', 'php_script_limit', 'plugins', 'public_search', 'useExiftool', 'version'])) {
						Configs::where('key', '=', $result->key)->update(['value' => $result->value ?? '']);
					}
				}
			} else {
				Logs::notice(__METHOD__, __LINE__, 'We are already passed migration point, ' . __CLASS__ . ' will not be applied.');
			}
		} else {
			Logs::notice(__FUNCTION__, __LINE__, env('DB_OLD_LYCHEE_PREFIX', '') . 'lychee_settings does not exist!');
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Logs::warning(__METHOD__, __LINE__, 'There is no going back for ' . __CLASS__ . '! HUE HUE HUE');
	}
}
