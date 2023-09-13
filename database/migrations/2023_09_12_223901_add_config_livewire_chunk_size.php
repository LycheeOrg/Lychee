<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	public const IMAGE_PROCESSING = 'Image Processing';
	public const INT = 'int';

	public const CONFIGS = [
		[
			'key' => 'upload_chunk_size',
			'value' => '0',
			'confidentiality' => '0',
			'cat' => self::IMAGE_PROCESSING,
			'type_range' => self::INT,
			'description' => 'Size of chunks when uploading in bytes: 0 is auto',
		],
	];

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('configs')->insert(self::CONFIGS);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		$keys = collect(self::CONFIGS)->map(fn ($v) => $v['key'])->all();

		DB::table('configs')->whereIn('key', $keys)->delete();
	}
};
