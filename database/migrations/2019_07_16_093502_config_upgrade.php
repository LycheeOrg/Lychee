<?php

use App\Configs;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class ConfigUpgrade extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('configs')) {
			Configs::where('key', '=', 'checkForUpdates')->update(['key' => 'check_for_updates']);
			Configs::where('key', '=', 'sortingPhotos_col')->update(['key' => 'sorting_Photos_col']);
			Configs::where('key', '=', 'sortingPhotos_order')->update(['key' => 'sorting_Photos_order']);
			Configs::where('key', '=', 'sortingAlbums_col')->update(['key' => 'sorting_Albums_col']);
			Configs::where('key', '=', 'sortingAlbums_order')->update(['key' => 'sorting_Albums_order']);
			Configs::where('key', '=', 'skipDuplicates')->update(['key' => 'skip_duplicates']);
			Configs::where('key', '=', 'deleteImported')->update(['key' => 'delete_imported']);
		} else {
			echo "Table configs does not exists\n";
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
			Configs::where('key', '=', 'check_for_updates')->update(['key' => 'checkForUpdates']);
			Configs::where('key', '=', 'sorting_Photos_col')->update(['key' => 'sortingPhotos_col']);
			Configs::where('key', '=', 'sorting_Photos_order')->update(['key' => 'sortingPhotos_order']);
			Configs::where('key', '=', 'sorting_Albums_col')->update(['key' => 'sortingAlbums_col']);
			Configs::where('key', '=', 'sorting_Albums_order')->update(['key' => 'sortingAlbums_order']);
			Configs::where('key', '=', 'skip_Duplicates')->update(['key' => 'skipDuplicates']);
			Configs::where('key', '=', 'delete_Imported')->update(['key' => 'deleteImported']);
		}
	}
}
