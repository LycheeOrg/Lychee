<?php

use App\Models\Configs;
use Illuminate\Database\Migrations\Migration;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Configs::where('key', 'sorting_Albums_col')->update(['key' => 'sorting_albums_col']);
		Configs::where('key', 'sorting_Albums_order')->update(['key' => 'sorting_albums_order']);
		Configs::where('key', 'sorting_Photos_col')->update(['key' => 'sorting_photos_col']);
		Configs::where('key', 'sorting_Photos_order')->update(['key' => 'sorting_photos_order']);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Configs::where('key', 'sorting_albums_col')->update(['key' => 'sorting_Albums_col']);
		Configs::where('key', 'sorting_albums_order')->update(['key' => 'sorting_Albums_order']);
		Configs::where('key', 'sorting_photos_col')->update(['key' => 'sorting_Photos_col']);
		Configs::where('key', 'sorting_photos_order')->update(['key' => 'sorting_Photos_order']);
	}
};
