<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('configs')->insert([
			'key' => 'random_album_id',
			'value' => 'starred',
			'cat' => 'Mod Frame',
			'type_range' => 'string',
			'confidentiality' => '0',
			'description' => 'Album id to be used by for random function.',
		]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', '=', 'random_album_id')->delete();
	}
};
