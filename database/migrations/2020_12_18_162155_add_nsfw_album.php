<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	private const ALBUM = 'albums';
	private const NSFW_COLUMN_NAME = 'nsfw';
	private const VIEWABLE = 'viewable';
	private const VISIBLE_HIDDEN = 'visible_hidden';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		defined('STRING') or define('STRING', 'string');
		defined('STRING_REQ') or define('STRING_REQ', 'string_required');
		defined('BOOL') or define('BOOL', '0|1');

		Schema::table(self::ALBUM, function ($table) {
			$table->boolean(self::NSFW_COLUMN_NAME)->default(false)->after(self::VISIBLE_HIDDEN);
		});
		Schema::table(self::ALBUM, function (Blueprint $table) {
			$table->renameColumn(self::VISIBLE_HIDDEN, self::VIEWABLE);
		});

		DB::table('configs')->insert([
			['key' => 'nsfw_visible', 'value' => '1', 'cat' => 'Mod NSFW', 'confidentiality' => '0', 'type_range' => BOOL],
			['key' => 'nsfw_blur', 'value' => '0', 'cat' => 'Mod NSFW', 'confidentiality' => '0', 'type_range' => BOOL],
			['key' => 'nsfw_warning', 'value' => '0', 'cat' => 'Mod NSFW', 'confidentiality' => '0', 'type_range' => BOOL],
			['key' => 'nsfw_warning_admin', 'value' => '0', 'cat' => 'Mod NSFW', 'confidentiality' => '0', 'type_range' => BOOL],
			['key' => 'nsfw_warning_text', 'value' => '<h1>Sensitive content</h1><p>This album contains sensitive content which some people may find offensive or disturbing.</p><p>Tap to consent.</p>', 'cat' => 'Mod NSFW', 'confidentiality' => '3', 'type_range' => STRING_REQ],
		]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table(self::ALBUM, function (Blueprint $table) {
			$table->dropColumn(self::NSFW_COLUMN_NAME);
		});
		Schema::table(self::ALBUM, function (Blueprint $table) {
			$table->renameColumn(self::VIEWABLE, self::VISIBLE_HIDDEN);
		});

		DB::table('configs')->where('cat', '=', 'Mod NSFW')->delete();
	}
};
