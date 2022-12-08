<?php

/** @noinspection PhpUndefinedClassInspection */

use App\Models\Configs;
use App\Models\Logs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ConfigFix extends Migration
{
	public const GALLERY = 'Gallery';
	public const ADMIN = 'Admin';
	public const IMAGE_PROCESSING = 'Image Processing';
	public const MOD_WELCOME = 'Mod Welcome';
	public const MOD_FRAME = 'Mod Frame';
	public const CONFIG = 'config';
	public const SMART_ALBUMS = 'Smart Albums';
	public const SYMBOLIC_LINK = 'Symbolic Link';

	/**
	 * Create the table if it did not exists yet.
	 */
	private function create()
	{
		if (!Schema::hasTable('configs')) {
			Schema::create('configs', function (Blueprint $table) {
				$table->increments('id');
				$table->string('key', 50);
				$table->string('value', 200)->nullable();
			});
		}
	}

	/**
	 * Update names with Snake Case.
	 */
	private function update_names()
	{
		Configs::where('key', '=', 'justified_layout')->update(['key' => 'layout']);
		Configs::where('key', '=', 'checkForUpdates')->update(['key' => 'check_for_updates']);
		Configs::where('key', '=', 'sortingPhotos_col')->update(['key' => 'sorting_Photos_col']);
		Configs::where('key', '=', 'sortingPhotos_order')->update(['key' => 'sorting_Photos_order']);
		Configs::where('key', '=', 'sortingAlbums_col')->update(['key' => 'sorting_Albums_col']);
		Configs::where('key', '=', 'sortingAlbums_order')->update(['key' => 'sorting_Albums_order']);
		Configs::where('key', '=', 'skipDuplicates')->update(['key' => 'skip_duplicates']);
		Configs::where('key', '=', 'deleteImported')->update(['key' => 'delete_imported']);
		Configs::where('key', '=', 'dropboxKey')->update(['key' => 'dropbox_key']);
	}

	/**
	 * Cleaning up entries which do not exists anymore.
	 *
	 * @param array $values
	 */
	private function cleanup(array &$values)
	{
		function get_key($v)
		{
			return $v['key'];
		}

		$keys = array_map('get_key', $values);

		try {
			Configs::whereNotIn('key', $keys)->delete();
		} catch (Exception $e) {
			Logs::warning(__FUNCTION__, __LINE__, 'Something weird happened.');
		}
	}

	/**
	 * Add potentially missing columns.
	 */
	private function missing_columns()
	{
		if (!Schema::hasColumn('configs', 'cat')) {
			Schema::table('configs', function (Blueprint $table) {
				$table->string('cat', 50)->after('value')->default('Config');
			});
		}
		if (!Schema::hasColumn('configs', 'confidentiality')) {
			Schema::table('configs', function (Blueprint $table) {
				$table->tinyInteger('confidentiality')->after('cat')->default(0);
			});
		}
		if (!Schema::hasColumn('configs', 'type_range')) {
			Schema::table('configs', function (Blueprint $table) {
				$table->string('type_range')->after('cat')->default('0|1');
				$table->string('description')->after('confidentiality')->default('');
			});
		}
	}

	/**
	 * Update the fields which are missing, set up the correct values for columns.
	 *
	 * @param array $default_values
	 */
	private function update_missing_fields(array &$default_values)
	{
		foreach ($default_values as $value) {
			$c = Configs::where('key', $value['key'])->count();
			$config = Configs::updateOrCreate(
				['key' => $value['key']],
				[
					'cat' => $value['cat'],
					'type_range' => $value['type_range'],
					'confidentiality' => $value['confidentiality'],
				]
			);
			if ($c === 0) {
				$config->value = $value['value'];
				$config->save();
			}
		}
	}

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		defined('INT') or define('INT', 'int');
		defined('STRING') or define('STRING', 'string');
		defined('STRING_REQ') or define('STRING_REQ', 'string_required');
		defined('BOOL') or define('BOOL', '0|1');
		defined('TERNARY') or define('TERNARY', '0|1|2');
		defined('DISABLED') or define('DISABLED', '');

		$default_values = [
			[
				'key' => 'version',
				'value' => '040000',
				'cat' => self::ADMIN,
				'type_range' => INT,
				'confidentiality' => '0',
			],
			[
				'key' => 'username',
				'value' => '',
				'cat' => self::ADMIN,
				'type_range' => STRING_REQ,
				'confidentiality' => '4',
			],
			[
				'key' => 'password',
				'value' => '',
				'cat' => self::ADMIN,
				'type_range' => STRING_REQ,
				'confidentiality' => '4',
			],
			[
				'key' => 'check_for_updates',
				'value' => '0',
				'cat' => self::ADMIN,
				'type_range' => BOOL,
				'confidentiality' => '0',
			],
			[
				'key' => 'sorting_Photos_col',
				'value' => 'takestamp',
				'cat' => self::GALLERY,
				'type_range' => 'id|takestamp|title|description|public|star|type',
				'confidentiality' => '2',
			],
			[
				'key' => 'sorting_Photos_order',
				'value' => 'ASC',
				'cat' => self::GALLERY,
				'type_range' => 'ASC|DESC',
				'confidentiality' => '2',
			],
			[
				'key' => 'sorting_Albums_col',
				'value' => 'max_takestamp',
				'cat' => self::GALLERY,
				'type_range' => 'id|title|description|public|max_takestamp|min_takestamp|created_at',
				'confidentiality' => '2',
			],
			[
				'key' => 'sorting_Albums_order',
				'value' => 'ASC',
				'cat' => self::GALLERY,
				'type_range' => 'ASC|DESC',
				'confidentiality' => '2',
			],
			[
				'key' => 'imagick',
				'value' => '1',
				'cat' => self::IMAGE_PROCESSING,
				'type_range' => BOOL,
				'confidentiality' => '2',
			],
			[
				'key' => 'dropbox_key',
				'value' => '',
				'cat' => self::ADMIN,
				'type_range' => STRING,
				'confidentiality' => '3',
			],
			[
				'key' => 'skip_duplicates',
				'value' => '0',
				'cat' => self::IMAGE_PROCESSING,
				'type_range' => BOOL,
				'confidentiality' => '2',
			],
			[
				'key' => 'lang',
				'value' => 'en',
				'cat' => self::GALLERY,
				'type_range' => DISABLED,
				'confidentiality' => '0',
			],
			[
				'key' => 'layout',
				'value' => '1',
				'cat' => self::GALLERY,
				'type_range' => TERNARY,
				'confidentiality' => '0',
			],
			[
				'key' => 'image_overlay',
				'value' => '1',
				'cat' => self::GALLERY,
				'type_range' => BOOL,
				'confidentiality' => '0',
			],
			[
				'key' => 'default_license',
				'value' => 'none',
				'cat' => self::GALLERY,
				'type_range' => STRING_REQ,
				'confidentiality' => '2',
			],
			[
				'key' => 'small_max_width',
				'value' => '0',
				'cat' => self::IMAGE_PROCESSING,
				'type_range' => INT,
				'confidentiality' => '2',
			],
			[
				'key' => 'small_max_height',
				'value' => '360',
				'cat' => self::IMAGE_PROCESSING,
				'type_range' => INT,
				'confidentiality' => '2',
			],
			[
				'key' => 'medium_max_width',
				'value' => '1920',
				'cat' => self::IMAGE_PROCESSING,
				'type_range' => INT,
				'confidentiality' => '2',
			],
			[
				'key' => 'medium_max_height',
				'value' => '1080',
				'cat' => self::IMAGE_PROCESSING,
				'type_range' => INT,
				'confidentiality' => '2',
			],
			[
				'key' => 'full_photo',
				'value' => '1',
				'cat' => self::GALLERY,
				'type_range' => BOOL,
				'confidentiality' => '0',
			],
			[
				'key' => 'delete_imported',
				'value' => '0',
				'cat' => self::IMAGE_PROCESSING,
				'type_range' => BOOL,
				'confidentiality' => '2',
			],
			[
				'key' => 'Mod_Frame',
				'value' => '1',
				'cat' => self::MOD_FRAME,
				'type_range' => BOOL,
				'confidentiality' => '0',
			],
			[
				'key' => 'Mod_Frame_refresh',
				'value' => '30000',
				'cat' => self::MOD_FRAME,
				'type_range' => INT,
				'confidentiality' => '0',
			],
			[
				'key' => 'image_overlay_type',
				'value' => 'desc',
				'cat' => self::GALLERY,
				'type_range' => 'exif|desc|takedate',
				'confidentiality' => '0',
			],
			[
				'key' => 'compression_quality',
				'value' => '90',
				'cat' => self::IMAGE_PROCESSING,
				'type_range' => INT,
				'confidentiality' => '2',
			],
			[
				'key' => 'landing_page_enable',
				'value' => '0',
				'cat' => self::MOD_WELCOME,
				'type_range' => BOOL,
				'confidentiality' => '0',
			],
			[
				'key' => 'landing_owner',
				'value' => 'John Smith',
				'cat' => self::MOD_WELCOME,
				'type_range' => STRING,
				'confidentiality' => '2',
			],
			[
				'key' => 'landing_title',
				'value' => 'John Smith',
				'cat' => self::MOD_WELCOME,
				'type_range' => STRING,
				'confidentiality' => '2',
			],
			[
				'key' => 'landing_subtitle',
				'value' => 'Cats, Dogs & Humans Photography',
				'cat' => self::MOD_WELCOME,
				'type_range' => STRING,
				'confidentiality' => '2',
			],
			[
				'key' => 'landing_facebook',
				'value' => 'https://www.facebook.com/JohnSmith',
				'cat' => self::MOD_WELCOME,
				'type_range' => STRING,
				'confidentiality' => '2',
			],
			[
				'key' => 'landing_flickr',
				'value' => 'https://www.flickr.com/JohnSmith',
				'cat' => self::MOD_WELCOME,
				'type_range' => STRING,
				'confidentiality' => '2',
			],
			[
				'key' => 'landing_twitter',
				'value' => 'https://www.twitter.com/JohnSmith',
				'cat' => self::MOD_WELCOME,
				'type_range' => STRING,
				'confidentiality' => '2',
			],
			[
				'key' => 'landing_instagram',
				'value' => 'https://instagram.com/JohnSmith',
				'cat' => self::MOD_WELCOME,
				'type_range' => STRING,
				'confidentiality' => '2',
			],
			[
				'key' => 'landing_youtube',
				'value' => 'https://www.youtube.com/JohnSmith',
				'cat' => self::MOD_WELCOME,
				'type_range' => STRING,
				'confidentiality' => '2',
			],
			[
				'key' => 'landing_background',
				'value' => 'dist/cat.jpg',
				'cat' => self::MOD_WELCOME,
				'type_range' => STRING,
				'confidentiality' => '2',
			],
			[
				'key' => 'thumb_2x',
				'value' => '1',
				'cat' => self::IMAGE_PROCESSING,
				'type_range' => BOOL,
				'confidentiality' => '2',
			],
			[
				'key' => 'small_2x',
				'value' => '1',
				'cat' => self::IMAGE_PROCESSING,
				'type_range' => BOOL,
				'confidentiality' => '2',
			],
			[
				'key' => 'medium_2x',
				'value' => '1',
				'cat' => self::IMAGE_PROCESSING,
				'type_range' => BOOL,
				'confidentiality' => '2',
			],
			[
				'key' => 'site_title',
				'value' => 'Lychee v4',
				'cat' => self::CONFIG,
				'type_range' => STRING,
				'confidentiality' => '0',
			],
			[
				'key' => 'site_copyright_enable',
				'value' => '1',
				'cat' => self::CONFIG,
				'type_range' => BOOL,
				'confidentiality' => '2',
			],
			[
				'key' => 'site_copyright_begin',
				'value' => '2019',
				'cat' => self::CONFIG,
				'type_range' => INT,
				'confidentiality' => '2',
			],
			[
				'key' => 'site_copyright_end',
				'value' => '2019',
				'cat' => self::CONFIG,
				'type_range' => INT,
				'confidentiality' => '2',
			],
			[
				'key' => 'api_key',
				'value' => '',
				'cat' => self::ADMIN,
				'type_range' => STRING,
				'confidentiality' => '3',
			],
			[
				'key' => 'allow_online_git_pull',
				'value' => '1',
				'cat' => self::ADMIN,
				'type_range' => BOOL,
				'confidentiality' => '3',
			],
			[
				'key' => 'force_migration_in_production',
				'value' => '0',
				'cat' => self::ADMIN,
				'type_range' => BOOL,
				'confidentiality' => '3',
			],
			[
				'key' => 'additional_footer_text',
				'value' => '',
				'cat' => self::CONFIG,
				'type_range' => STRING,
				'confidentiality' => '2',
			],
			[
				'key' => 'display_social_in_gallery',
				'value' => '0',
				'cat' => self::CONFIG,
				'type_range' => BOOL,
				'confidentiality' => '2',
			],
			[
				'key' => 'public_search',
				'value' => '0',
				'cat' => self::CONFIG,
				'type_range' => BOOL,
				'confidentiality' => '0',
			],
			[
				'key' => 'gen_demo_js',
				'value' => '0',
				'cat' => self::ADMIN,
				'type_range' => BOOL,
				'confidentiality' => '3',
			],
			[
				'key' => 'hide_version_number',
				'value' => '0',
				'cat' => self::CONFIG,
				'type_range' => BOOL,
				'confidentiality' => '3',
			],
			[
				'key' => 'SL_enable',
				'value' => '0',
				'cat' => self::SYMBOLIC_LINK,
				'type_range' => BOOL,
				'confidentiality' => '3',
			],
			[
				'key' => 'SL_for_admin',
				'value' => '0',
				'cat' => self::SYMBOLIC_LINK,
				'type_range' => BOOL,
				'confidentiality' => '3',
			],
			[
				'key' => 'SL_life_time_days',
				'value' => '7',
				'cat' => self::SYMBOLIC_LINK,
				'type_range' => INT,
				'confidentiality' => '3',
			],
			[
				'key' => 'public_recent',
				'value' => '0',
				'cat' => self::SMART_ALBUMS,
				'type_range' => BOOL,
				'confidentiality' => '0',
			],
			[
				'key' => 'recent_age',
				'value' => '1',
				'cat' => self::SMART_ALBUMS,
				'type_range' => INT,
				'confidentiality' => '2',
			],
			[
				'key' => 'public_starred',
				'value' => '0',
				'cat' => self::SMART_ALBUMS,
				'type_range' => BOOL,
				'confidentiality' => '0',
			],
			[
				'key' => 'downloadable',
				'value' => '0',
				'cat' => self::CONFIG,
				'type_range' => BOOL,
				'confidentiality' => '0',
			],
			[
				'key' => 'photos_wraparound',
				'value' => '1',
				'cat' => self::GALLERY,
				'type_range' => BOOL,
				'confidentiality' => '0',
			],
			[
				'key' => 'raw_formats',
				'value' => '.tex',
				'cat' => self::CONFIG,
				'type_range' => DISABLED,
				'confidentiality' => '3',
			],
			[
				'key' => 'map_display',
				'value' => '0',
				'cat' => self::GALLERY,
				'type_range' => BOOL,
				'confidentiality' => '0',
			],
			[
				'key' => 'zip64',
				'value' => '1',
				'cat' => self::CONFIG,
				'type_range' => BOOL,
				'confidentiality' => '0',
			],
		];

		$this->create();
		$this->update_names();
		$this->cleanup($default_values);
		$this->missing_columns();
		$this->update_missing_fields($default_values);
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
