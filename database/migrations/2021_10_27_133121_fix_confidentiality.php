<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::table('configs')->where('key', 'editor_enabled')->update(['confidentiality' => '0']);
		DB::table('configs')->where('key', 'upload_processing_limit')->update(['confidentiality' => '0']);
		DB::table('configs')->where('key', 'public_photos_hidden')->update(['confidentiality' => '0']);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::table('configs')->where('key', 'editor_enabled')->update(['confidentiality' => '2']);
		DB::table('configs')->where('key', 'upload_processing_limit')->update(['confidentiality' => '2']);
		DB::table('configs')->where('key', 'public_photos_hidden')->update(['confidentiality' => '2']);
	}
};
