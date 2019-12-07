<?php

/** @noinspection PhpUndefinedClassInspection */
use App\Configs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ConfigFix extends Migration
{
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
		Configs::where('key', 'justified_layout')
			->update(['key' => 'layout']);
		Configs::where('key', '=', 'checkForUpdates')
			->update(['key' => 'check_for_updates']);
		Configs::where('key', '=', 'sortingPhotos_col')
			->update(['key' => 'sorting_Photos_col']);
		Configs::where('key', '=', 'sortingPhotos_order')
			->update(['key' => 'sorting_Photos_order']);
		Configs::where('key', '=', 'sortingAlbums_col')
			->update(['key' => 'sorting_Albums_col']);
		Configs::where('key', '=', 'sortingAlbums_order')
			->update(['key' => 'sorting_Albums_order']);
		Configs::where('key', '=', 'skipDuplicates')
			->update(['key' => 'skip_duplicates']);
		Configs::where('key', '=', 'deleteImported')
			->update(['key' => 'delete_imported']);
		Configs::where('key', '=', 'dropboxKey')
			->update(['key' => 'dropbox_key']);
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
			echo "Something weird happened.\n";
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
				$table->tinyInteger('confidentiality')->after('cat')
					->default(0);
			});
		}
		if (!Schema::hasColumn('configs', 'type_range')) {
			Schema::table('configs', function (Blueprint $table) {
				$table->string('type_range')->after('cat')->default('0|1');
				$table->string('description')->after('confidentiality')
					->default('');
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
			$config = Configs::updateOrCreate(['key' => $value['key']],
				[
					'cat' => $value['cat'],
					'type_range' => $value['type_range'],
					'confidentiality' => $value['confidentiality'],
				]);
			if ($c == 0) {
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
		define('INT', 'int');
		define('STRING', 'string');
		define('STRING_REQ', 'string_required');
		define('BOOL', '0|1');
		define('TERNARY', '0|1|2');
		define('DISABLED', '');

		$default_values = [
			[
				'key' => 'version',
				'value' => '040000',
				'cat' => 'Admin',
				'type_range' => INT,
				'confidentiality' => '0',
			],
			[
				'key' => 'username',
				'value' => '',
				'cat' => 'Admin',
				'type_range' => STRING_REQ,
				'confidentiality' => '4',
			],
			[
				'key' => 'password',
				'value' => '',
				'cat' => 'Admin',
				'type_range' => STRING_REQ,
				'confidentiality' => '4',
			],
			[
				'key' => 'check_for_updates',
				'value' => '0',
				'cat' => 'Admin',
				'type_range' => BOOL,
				'confidentiality' => '0',
			],
			[
				'key' => 'sorting_Photos_col',
				'value' => 'takestamp',
				'cat' => 'Gallery',
				'type_range' => 'id|takestamp|title|description|public|star|type',
				'confidentiality' => '2',
			],
			[
				'key' => 'sorting_Photos_order',
				'value' => 'ASC',
				'cat' => 'Gallery',
				'type_range' => 'ASC|DESC',
				'confidentiality' => '2',
			],
			[
				'key' => 'sorting_Albums_col',
				'value' => 'max_takestamp',
				'cat' => 'Gallery',
				'type_range' => 'id|title|description|public|max_takestamp|min_takestamp|created_at',
				'confidentiality' => '2',
			],
			[
				'key' => 'sorting_Albums_order',
				'value' => 'ASC',
				'cat' => 'Gallery',
				'type_range' => 'ASC|DESC',
				'confidentiality' => '2',
			],
			[
				'key' => 'imagick',
				'value' => '1',
				'cat' => 'Image Processing',
				'type_range' => BOOL,
				'confidentiality' => '2',
			],
			[
				'key' => 'dropbox_key',
				'value' => '',
				'cat' => 'Admin',
				'type_range' => STRING,
				'confidentiality' => '3',
			],
			[
				'key' => 'skip_duplicates',
				'value' => '0',
				'cat' => 'Image Processing',
				'type_range' => BOOL,
				'confidentiality' => '2',
			],
			[
				'key' => 'lang',
				'value' => 'en',
				'cat' => 'Gallery',
				'type_range' => DISABLED,
				'confidentiality' => '0',
			],
			[
				'key' => 'layout',
				'value' => '1',
				'cat' => 'Gallery',
				'type_range' => TERNARY,
				'confidentiality' => '0',
			],
			[
				'key' => 'image_overlay',
				'value' => '1',
				'cat' => 'Gallery',
				'type_range' => BOOL,
				'confidentiality' => '0',
			],
			[
				'key' => 'default_license',
				'value' => 'none',
				'cat' => 'Gallery',
				'type_range' => STRING_REQ,
				'confidentiality' => '2',
			],
			[
				'key' => 'small_max_width',
				'value' => '0',
				'cat' => 'Image Processing',
				'type_range' => INT,
				'confidentiality' => '2',
			],
			[
				'key' => 'small_max_height',
				'value' => '360',
				'cat' => 'Image Processing',
				'type_range' => INT,
				'confidentiality' => '2',
			],
			[
				'key' => 'medium_max_width',
				'value' => '1920',
				'cat' => 'Image Processing',
				'type_range' => INT,
				'confidentiality' => '2',
			],
			[
				'key' => 'medium_max_height',
				'value' => '1080',
				'cat' => 'Image Processing',
				'type_range' => INT,
				'confidentiality' => '2',
			],
			[
				'key' => 'full_photo',
				'value' => '1',
				'cat' => 'Gallery',
				'type_range' => BOOL,
				'confidentiality' => '0',
			],
			[
				'key' => 'delete_imported',
				'value' => '0',
				'cat' => 'Image Processing',
				'type_range' => BOOL,
				'confidentiality' => '2',
			],
			[
				'key' => 'Mod_Frame',
				'value' => '1',
				'cat' => 'Mod Frame',
				'type_range' => BOOL,
				'confidentiality' => '0',
			],
			[
				'key' => 'Mod_Frame_refresh',
				'value' => '30000',
				'cat' => 'Mod Frame',
				'type_range' => INT,
				'confidentiality' => '0',
			],
			[
				'key' => 'image_overlay_type',
				'value' => 'desc',
				'cat' => 'Gallery',
				'type_range' => 'exif|desc|takedate',
				'confidentiality' => '0',
			],
			[
				'key' => 'compression_quality',
				'value' => '90',
				'cat' => 'Image Processing',
				'type_range' => INT,
				'confidentiality' => '2',
			],
			[
				'key' => 'landing_page_enable',
				'value' => '1',
				'cat' => 'Mod Welcome',
				'type_range' => BOOL,
				'confidentiality' => '0',
			],
			[
				'key' => 'landing_owner',
				'value' => 'John Smith',
				'cat' => 'Mod Welcome',
				'type_range' => STRING,
				'confidentiality' => '2',
			],
			[
				'key' => 'landing_title',
				'value' => 'John Smith',
				'cat' => 'Mod Welcome',
				'type_range' => STRING,
				'confidentiality' => '2',
			],
			[
				'key' => 'landing_subtitle',
				'value' => 'Cats, Dogs & Humans Photography',
				'cat' => 'Mod Welcome',
				'type_range' => STRING,
				'confidentiality' => '2',
			],
			[
				'key' => 'landing_facebook',
				'value' => 'https://www.facebook.com/JohnSmith',
				'cat' => 'Mod Welcome',
				'type_range' => STRING,
				'confidentiality' => '2',
			],
			[
				'key' => 'landing_flickr',
				'value' => 'https://www.flickr.com/JohnSmith',
				'cat' => 'Mod Welcome',
				'type_range' => STRING,
				'confidentiality' => '2',
			],
			[
				'key' => 'landing_twitter',
				'value' => 'https://www.twitter.com/JohnSmith',
				'cat' => 'Mod Welcome',
				'type_range' => STRING,
				'confidentiality' => '2',
			],
			[
				'key' => 'landing_instagram',
				'value' => 'https://instagram.com/JohnSmith',
				'cat' => 'Mod Welcome',
				'type_range' => STRING,
				'confidentiality' => '2',
			],
			[
				'key' => 'landing_youtube',
				'value' => 'https://www.youtube.com/JohnSmith',
				'cat' => 'Mod Welcome',
				'type_range' => STRING,
				'confidentiality' => '2',
			],
			[
				'key' => 'landing_background',
				'value' => 'dist/cat.jpg',
				'cat' => 'Mod Welcome',
				'type_range' => STRING,
				'confidentiality' => '2',
			],
			[
				'key' => 'thumb_2x',
				'value' => '1',
				'cat' => 'Image Processing',
				'type_range' => BOOL,
				'confidentiality' => '2',
			],
			[
				'key' => 'small_2x',
				'value' => '1',
				'cat' => 'Image Processing',
				'type_range' => BOOL,
				'confidentiality' => '2',
			],
			[
				'key' => 'medium_2x',
				'value' => '1',
				'cat' => 'Image Processing',
				'type_range' => BOOL,
				'confidentiality' => '2',
			],
			[
				'key' => 'site_title',
				'value' => 'Lychee v4',
				'cat' => 'config',
				'type_range' => STRING,
				'confidentiality' => '0',
			],
			[
				'key' => 'site_copyright_enable',
				'value' => '1',
				'cat' => 'config',
				'type_range' => BOOL,
				'confidentiality' => '2',
			],
			[
				'key' => 'site_copyright_begin',
				'value' => '2019',
				'cat' => 'config',
				'type_range' => INT,
				'confidentiality' => '2',
			],
			[
				'key' => 'site_copyright_end',
				'value' => '2019',
				'cat' => 'config',
				'type_range' => INT,
				'confidentiality' => '2',
			],
			[
				'key' => 'api_key',
				'value' => '',
				'cat' => 'Admin',
				'type_range' => STRING,
				'confidentiality' => '3',
			],
			[
				'key' => 'allow_online_git_pull',
				'value' => '1',
				'cat' => 'Admin',
				'type_range' => BOOL,
				'confidentiality' => '3',
			],
			[
				'key' => 'force_migration_in_production',
				'value' => '0',
				'cat' => 'Admin',
				'type_range' => BOOL,
				'confidentiality' => '3',
			],
			[
				'key' => 'additional_footer_text',
				'value' => '',
				'cat' => 'config',
				'type_range' => STRING,
				'confidentiality' => '2',
			],
			[
				'key' => 'display_social_in_gallery',
				'value' => '0',
				'cat' => 'config',
				'type_range' => BOOL,
				'confidentiality' => '2',
			],
			[
				'key' => 'public_search',
				'value' => '0',
				'cat' => 'config',
				'type_range' => BOOL,
				'confidentiality' => '0',
			],
			[
				'key' => 'gen_demo_js',
				'value' => '0',
				'cat' => 'Admin',
				'type_range' => BOOL,
				'confidentiality' => '3',
			],
			[
				'key' => 'hide_version_number',
				'value' => '0',
				'cat' => 'config',
				'type_range' => BOOL,
				'confidentiality' => '3',
			],
			[
				'key' => 'SL_enable',
				'value' => '1',
				'cat' => 'Symbolic Link',
				'type_range' => BOOL,
				'confidentiality' => '3',
			],
			[
				'key' => 'SL_for_admin',
				'value' => '1',
				'cat' => 'Symbolic Link',
				'type_range' => BOOL,
				'confidentiality' => '3',
			],
			[
				'key' => 'SL_life_time_days',
				'value' => '1',
				'cat' => 'Symbolic Link',
				'type_range' => INT,
				'confidentiality' => '3',
			],
			[
				'key' => 'public_recent',
				'value' => '0',
				'cat' => 'Smart Albums',
				'type_range' => BOOL,
				'confidentiality' => '0',
			],
			[
				'key' => 'recent_age',
				'value' => '1',
				'cat' => 'Smart Albums',
				'type_range' => INT,
				'confidentiality' => '2',
			],
			[
				'key' => 'public_starred',
				'value' => '0',
				'cat' => 'Smart Albums',
				'type_range' => BOOL,
				'confidentiality' => '0',
			],
			[
				'key' => 'downloadable',
				'value' => '0',
				'cat' => 'config',
				'type_range' => BOOL,
				'confidentiality' => '0',
			],
			[
				'key' => 'photos_wraparound',
				'value' => '1',
				'cat' => 'Gallery',
				'type_range' => BOOL,
				'confidentiality' => '0',
			],
			[
				'key' => 'raw_formats',
				'value' => '.tex',
				'cat' => 'config',
				'type_range' => DISABLED,
				'confidentiality' => '3',
			],
			[
				'key' => 'map_display',
				'value' => '0',
				'cat' => 'Gallery',
				'type_range' => BOOL,
				'confidentiality' => '0',
			],
			[
				'key' => 'zip64',
				'value' => '1',
				'cat' => 'config',
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
		echo "There is no going back! HUE HUE HUE\n";
	}
}
