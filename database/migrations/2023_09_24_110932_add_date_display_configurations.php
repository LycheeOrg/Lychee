<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	public const GALLERY = 'Gallery';
	public const STRING_REQ = 'string_required';

	public const CONFIGS = [
		[
			'key' => 'date_format_photo_thumb',
			'value' => 'M j, Y, g:i:s A e',
			'confidentiality' => '0',
			'cat' => self::GALLERY,
			'type_range' => self::STRING_REQ,
			'description' => 'Format the date for the photo thumbs. See https://www.php.net/manual/en/datetime.format.php',
		],
		[
			'key' => 'date_format_photo_overlay',
			'value' => 'M j, Y, g:i:s A e',
			'confidentiality' => '0',
			'cat' => self::GALLERY,
			'type_range' => self::STRING_REQ,
			'description' => 'Format the date for the photo overlay. See https://www.php.net/manual/en/datetime.format.php',
		],
		[
			'key' => 'date_format_sidebar_uploaded',
			'value' => 'M j, Y, g:i:s A e',
			'confidentiality' => '0',
			'cat' => self::GALLERY,
			'type_range' => self::STRING_REQ,
			'description' => 'Format the upload date for the photo sidebar. See https://www.php.net/manual/en/datetime.format.php',
		],
		[
			'key' => 'date_format_sidebar_taken_at',
			'value' => 'M j, Y, g:i:s A e',
			'confidentiality' => '0',
			'cat' => self::GALLERY,
			'type_range' => self::STRING_REQ,
			'description' => 'Format the capture date for the photo sidebar. See https://www.php.net/manual/en/datetime.format.php',
		],
		[
			'key' => 'date_format_hero_min_max',
			'value' => 'F Y',
			'confidentiality' => '0',
			'cat' => self::GALLERY,
			'type_range' => self::STRING_REQ,
			'description' => 'Format the date for the album hero. See https://www.php.net/manual/en/datetime.format.php',
		],
		[
			'key' => 'date_format_hero_created_at',
			'value' => 'M j, Y, g:i:s A T',
			'confidentiality' => '0',
			'cat' => self::GALLERY,
			'type_range' => self::STRING_REQ,
			'description' => 'Format the created date for the album details. See https://www.php.net/manual/en/datetime.format.php',
		],
		[
			'key' => 'date_format_album_thumb',
			'value' => 'M Y',
			'confidentiality' => '0',
			'cat' => self::GALLERY,
			'type_range' => self::STRING_REQ,
			'description' => 'Format the date for the album thumbs. See https://www.php.net/manual/en/datetime.format.php',
		],
	];

	/**
	 * Run the migrations.
	 *
	 * @return void
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
