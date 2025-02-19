<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	public const COL = 'expert';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table('configs', function (Blueprint $table): void {
			$table->boolean(self::COL)->default(false)->after('not_on_docker')->comment('Defines that this setting is only visible in expert view');
		});

		DB::table('configs')->whereIn('key',
			[
				'force_32bit_ids',
				'zip_deflate_level',
				'zip64',
				'date_format_album_thumb',
				'date_format_hero_created_at',
				'date_format_hero_min_max',
				'date_format_photo_overlay',
				'date_format_photo_thumb',
				'date_format_sidebar_taken_at',
				'date_format_sidebar_uploaded',
				'photo_layout_gap',
				'photo_layout_grid_column_width',
				'photo_layout_justified_row_height',
				'photo_layout_masonry_column_width',
				'photo_layout_square_column_width',
				'prefer_available_xmp_metadata',
				'timeline_album_date_format_day',
				'timeline_album_date_format_month',
				'timeline_album_date_format_year',
				'timeline_photo_date_format_day',
				'timeline_photo_date_format_hour',
				'timeline_photo_date_format_month',
				'timeline_photo_date_format_year',
				'location_decoding_timeout',
				'nsfw_banner_override',
				'cache_event_logging',
				'SL_for_admin',
				'disable_recursive_permission_check',
				'allow_online_git_pull',
				'apply_composer_update',
				'force_migration_in_production',
				'log_max_num_line',
				'maintenance_processing_limit',
			])->update([self::COL => true]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('configs', function (Blueprint $table) {
			$table->dropColumn(self::COL);
		});
	}
};
