<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	public const MOD_NSFW = 'Mod NSFW';
	public const BOOL = '0|1';

	public const CONFIGS = [
		[
			'key' => 'nsfw_banner_blur_backdrop',
			'value' => '0',
			'confidentiality' => '0',
			'cat' => self::MOD_NSFW,
			'type_range' => self::BOOL,
			'description' => 'Blur background instead of dark red opaque.',
		],
	];

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('configs')->insert(self::CONFIGS);
		DB::table('configs')->where('key', '=', 'nsfw_banner_override')->update(['description' => 'override description with personalized html']);
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
