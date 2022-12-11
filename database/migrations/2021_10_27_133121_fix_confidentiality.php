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
		Configs::where('key', 'editor_enabled')->update(['confidentiality' => '0']);
		Configs::where('key', 'upload_processing_limit')->update(['confidentiality' => '0']);
		Configs::where('key', 'public_photos_hidden')->update(['confidentiality' => '0']);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Configs::where('key', 'editor_enabled')->update(['confidentiality' => '2']);
		Configs::where('key', 'upload_processing_limit')->update(['confidentiality' => '2']);
		Configs::where('key', 'public_photos_hidden')->update(['confidentiality' => '2']);
	}
};
