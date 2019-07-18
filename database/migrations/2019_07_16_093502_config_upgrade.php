<?php

/** @noinspection PhpUndefinedClassInspection */
use App\Configs;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class ConfigUpgrade extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('configs')) {
			Configs::where('key', '=', 'checkForUpdates')->update(['key' => 'check_for_updates']);
			Configs::where('key', '=', 'sortingPhotos_col')->update(['key' => 'sorting_Photos_col']);
			Configs::where('key', '=', 'sortingPhotos_order')->update(['key' => 'sorting_Photos_order']);
			Configs::where('key', '=', 'sortingAlbums_col')->update(['key' => 'sorting_Albums_col']);
			Configs::where('key', '=', 'sortingAlbums_order')->update(['key' => 'sorting_Albums_order']);
			Configs::where('key', '=', 'skipDuplicates')->update(['key' => 'skip_duplicates']);
			Configs::where('key', '=', 'deleteImported')->update(['key' => 'delete_imported']);
			Configs::where('key', '=', 'dropboxKey')->update(['key' => 'dropbox_key']);

			define('INT', 'int');
			define('STRING', 'string');
			define('STRING_REQ', 'string_required');
			define('BOOL', '0|1');
			define('TERNARY', '0|1|2');
			define('DISABLED', '');

			Schema::table('configs', function (Blueprint $table) {
				$table->string('type_range')->after('cat')->default('0|1');
				$table->string('description')->after('confidentiality')->default('');
			});

			Configs::where('key', '=', 'version')->update(['type_range' => INT]);
			Configs::where('key', '=', 'username')->update(['type_range' => STRING_REQ]);
			Configs::where('key', '=', 'password')->update(['type_range' => STRING_REQ]);
			Configs::where('key', '=', 'check_for_updates')->update(['type_range' => BOOL]);
			Configs::where('key', '=', 'sorting_Photos_col')->update(['type_range' => 'id|takestamp|title|description|public|star|type']);
			Configs::where('key', '=', 'sorting_Photos_order')->update(['type_range' => 'ASC|DESC']);
			Configs::where('key', '=', 'sorting_Albums_col')->update(['type_range' => 'id|title|description|public|max_takestamp|min_takestamp']);
			Configs::where('key', '=', 'sorting_Albums_order')->update(['type_range' => 'ASC|DESC']);
			Configs::where('key', '=', 'imagick')->update(['type_range' => BOOL]);
			Configs::where('key', '=', 'dropbox_key')->update(['type_range' => STRING]);
			Configs::where('key', '=', 'skip_duplicates')->update(['type_range' => BOOL]);
			Configs::where('key', '=', 'lang')->update(['type_range' => DISABLED]);
			Configs::where('key', '=', 'layout')->update(['type_range' => TERNARY]);
			Configs::where('key', '=', 'image_overlay')->update(['type_range' => BOOL]);
			Configs::where('key', '=', 'image_overlay_type')->update(['type_range' => 'exif|desc|takedate']);
			Configs::where('key', '=', 'default_license')->update(['type_range' => STRING_REQ]);
			Configs::where('key', '=', 'small_max_width')->update(['type_range' => INT]);
			Configs::where('key', '=', 'small_max_height')->update(['type_range' => INT]);
			Configs::where('key', '=', 'medium_max_width')->update(['type_range' => INT]);
			Configs::where('key', '=', 'medium_max_height')->update(['type_range' => INT]);
			Configs::where('key', '=', 'full_photo')->update(['type_range' => BOOL]);
			Configs::where('key', '=', 'delete_imported')->update(['type_range' => BOOL]);
			Configs::where('key', '=', 'Mod_Frame')->update(['type_range' => BOOL]);
			Configs::where('key', '=', 'Mod_Frame_refresh')->update(['type_range' => INT]);
			Configs::where('key', '=', 'compression_quality')->update(['type_range' => INT]);
			Configs::where('key', '=', 'landing_page_enable')->update(['type_range' => BOOL]);
			Configs::where('key', '=', 'landing_owner')->update(['type_range' => STRING]);
			Configs::where('key', '=', 'landing_title')->update(['type_range' => STRING]);
			Configs::where('key', '=', 'landing_subtitle')->update(['type_range' => STRING]);
			Configs::where('key', '=', 'landing_facebook')->update(['type_range' => STRING]);
			Configs::where('key', '=', 'landing_flickr')->update(['type_range' => STRING]);
			Configs::where('key', '=', 'landing_twitter')->update(['type_range' => STRING]);
			Configs::where('key', '=', 'landing_instagram')->update(['type_range' => STRING]);
			Configs::where('key', '=', 'landing_youtube')->update(['type_range' => STRING]);
			Configs::where('key', '=', 'landing_background')->update(['type_range' => STRING]);
			Configs::where('key', '=', 'thumb_2x')->update(['type_range' => BOOL]);
			Configs::where('key', '=', 'small_2x')->update(['type_range' => BOOL]);
			Configs::where('key', '=', 'medium_2x')->update(['type_range' => BOOL]);
			Configs::where('key', '=', 'site_title')->update(['type_range' => STRING]);
			Configs::where('key', '=', 'site_copyright_enable')->update(['type_range' => BOOL]);
			Configs::where('key', '=', 'site_copyright_begin')->update(['type_range' => INT]);
			Configs::where('key', '=', 'site_copyright_end')->update(['type_range' => INT]);
			Configs::where('key', '=', 'api_key')->update(['type_range' => STRING]);
			Configs::where('key', '=', 'php_script_no_limit')->update(['type_range' => BOOL]);
			Configs::where('key', '=', 'allow_online_git_pull')->update(['type_range' => BOOL]);
			Configs::where('key', '=', 'force_migration_in_production')->update(['type_range' => BOOL]);
			Configs::where('key', '=', 'additional_footer_text')->update(['type_range' => STRING]);
			Configs::where('key', '=', 'display_social_in_gallery')->update(['type_range' => BOOL]);
			Configs::where('key', '=', 'public_search')->update(['type_range' => BOOL]);
			Configs::where('key', '=', 'gen_demo_js')->update(['type_range' => BOOL]);
			Configs::where('key', '=', 'hide_version_number')->update(['type_range' => BOOL]);
			Configs::where('key', '=', 'SL_enable')->update(['type_range' => BOOL]);
			Configs::where('key', '=', 'SL_for_admin')->update(['type_range' => BOOL]);
			Configs::where('key', '=', 'SL_life_time_days')->update(['type_range' => INT]);
			Configs::where('key', '=', 'public_recent')->update(['type_range' => BOOL]);
			Configs::where('key', '=', 'recent_age')->update(['type_range' => INT]);
			Configs::where('key', '=', 'public_starred')->update(['type_range' => BOOL]);
			Configs::where('key', '=', 'downloadable')->update(['type_range' => BOOL]);
			Configs::where('key', '=', 'photos_wraparound')->update(['type_range' => BOOL]);
		} else {
			echo "Table configs does not exists\n";
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		if (env('DB_DROP_CLEAR_TABLES_ON_ROLLBACK', false)) {
			Configs::where('key', '=', 'check_for_updates')->update(['key' => 'checkForUpdates']);
			Configs::where('key', '=', 'sorting_Photos_col')->update(['key' => 'sortingPhotos_col']);
			Configs::where('key', '=', 'sorting_Photos_order')->update(['key' => 'sortingPhotos_order']);
			Configs::where('key', '=', 'sorting_Albums_col')->update(['key' => 'sortingAlbums_col']);
			Configs::where('key', '=', 'sorting_Albums_order')->update(['key' => 'sortingAlbums_order']);
			Configs::where('key', '=', 'skip_duplicates')->update(['key' => 'skipDuplicates']);
			Configs::where('key', '=', 'delete_imported')->update(['key' => 'deleteImported']);
			Configs::where('key', '=', 'dropbox_key')->update(['key' => 'dropboxKey']);

			Schema::table('configs', function (Blueprint $table) {
				$table->dropColumn(['type_range', 'description']);
			});
		}
	}
}
