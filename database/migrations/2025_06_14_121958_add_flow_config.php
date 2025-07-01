<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\AbstractBaseConfigMigration;
use Illuminate\Database\Schema\Blueprint;

return new class() extends AbstractBaseConfigMigration {
	public const CAT = 'Mod Flow';
	public const NSFW = 'Mod NSFW';
	public const STRING_REQ = 'string_required';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		$type_range = DB::table('configs')->select('type_range')->where('key', 'home_page_default')->first()->type_range;
		DB::table('configs')->where('key', 'home_page_default')->update(['type_range' => $type_range . '|flow']);

		DB::table('config_categories')->insert([
			'cat' => 'Mod Flow',
			'name' => 'Flow',
			'description' => 'This module enables the displays of albums in a feed-like manner. Only albums with photos will be displayed, albums with only children are not included in the Flow. Being a pure display, the Flow page does not allow users to upload, move, <i>etc.</i>',
			'order' => 21,
		]);

		DB::table('configs')->insert($this->getConfigs());

		Schema::table('base_albums', function (Blueprint $table) {
			$table->dateTime('published_at', 0)->nullable(true)->after('updated_at')->index();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('base_albums', function (Blueprint $table) {
			$table->dropIndex('base_albums_published_at_index');
		});
		Schema::table('base_albums', function (Blueprint $table) {
			$table->dropColumn('published_at');
		});

		$keys = collect($this->getConfigs())->map(fn ($v) => $v['key'])->all();
		DB::table('configs')->whereIn('key', $keys)->delete();

		DB::table('config_categories')->where('cat', 'Mod Flow')->delete();

		$type_range = DB::table('configs')->select('type_range')->where('key', 'home_page_default')->first()->type_range;
		DB::table('configs')->where('key', 'home_page_default')->update(['type_range' => str_replace('|flow', '', $type_range)]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'flow_enabled',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL, // We will change the type_range later when adding for functionalities.
				'description' => 'Enable Flow display',
				'details' => '',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 1,
			],
			[
				'key' => 'flow_public',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL, // We will change the type_range later when adding for functionalities.
				'description' => 'Allows anonymous user to access the Flow',
				'details' => '',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 2,
			],
			[
				'key' => 'flow_base',
				'value' => '',
				'cat' => self::CAT,
				'type_range' => self::STRING,
				'description' => 'Base album id for the flow',
				'details' => 'All albums within this album will be included in the flow (leave empty for root).',
				'is_secret' => false,
				'is_expert' => true,
				'level' => 0,
				'order' => 3,
			],
			[
				'key' => 'flow_min_max_order',
				'value' => 'older_younger',
				'cat' => self::CAT,
				'type_range' => 'older_younger|younger_older',
				'description' => 'Set which min-max date to display first.',
				'details' => 'If set to "older_younger", the older date will be displayed first, otherwise the younger date will be displayed first.',
				'is_secret' => true,
				'is_expert' => true,
				'level' => 0,
				'order' => 4,
			],
			[
				'key' => 'flow_max_items',
				'value' => '10',
				'cat' => self::CAT,
				'type_range' => self::POSITIVE,
				'description' => 'Maximum number of items in the flow',
				'details' => 'A lower number will require more requests, a higher number will consume more memory.',
				'is_secret' => false,
				'is_expert' => true,
				'level' => 0,
				'order' => 5,
			],
			[
				'key' => 'flow_strategy',
				'value' => 'auto',
				'cat' => self::CAT,
				'type_range' => 'auto|opt-in',
				'description' => 'Flow strategy',
				'details' => 'Choose how the flow is generated. "auto" will include all albums, "opt-in" will only include albums that have the flow enabled.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 1,
				'order' => 6,
			],
			[
				'key' => 'flow_include_sub_albums',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Include sub-albums in the flow',
				'details' => 'All the descendants of the base album will be included in the flow. If disabled, only the direct children of the base album will be included.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 1,
				'order' => 7,
			],
			[
				'key' => 'flow_include_photos_from_children',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Include photos from children albums',
				'details' => 'If an album has no photos, but has children, the photos from the children will be displayed.<br>
				<span class="pi pi-exclamation-triangle text-orange-500"></span> This is NOT recommended. Consequences includes memory exhaution, slower loading time, crashes...',
				'is_secret' => false,
				'is_expert' => true,
				'level' => 1,
				'order' => 8,
			],
			[
				'key' => 'flow_open_album_on_click',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Open album on click',
				'details' => 'Go to the album when clicked. If disabled, the photos will be displayed directly.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 1,
				'order' => 9,
			],
			[
				'key' => 'flow_display_open_album_button',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Display open album button',
				'details' => 'A button to open the album will be displayed in the card.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 1,
				'order' => 10,
			],
			[
				'key' => 'flow_highlight_first_picture',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Highlight first picture in the album instead of the album cover',
				'details' => 'The main picture displayed is the first picture in the album.',
				'is_secret' => false,
				'is_expert' => true,
				'level' => 1,
				'order' => 11,
			],
			[
				'key' => 'flow_min_max_enabled',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Enable min-max date in the flow',
				'details' => 'Display the min-max date from the photos of the album in the flow.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 1,
				'order' => 12,
			],
			[
				'key' => 'flow_display_statistics',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Display album statistics in the flow',
				'details' => 'The number of views, shares, and downloads of the album will be displayed.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 1,
				'order' => 13,
			],
			[
				'key' => 'flow_compact_mode_enabled',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Enable flow compact mode',
				'details' => 'Clamp the description to 3 lines and hides exttra information like the number of photos and children.<br>Also adds a "Show more" button to expand.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 1,
				'order' => 14,
			],
			[
				'key' => 'flow_image_header_enabled',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Enable image header in the flow',
				'details' => 'The top of the card will highlight the cover of the album.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 1,
				'order' => 15,
			],
			[
				'key' => 'flow_image_header_cover',
				'value' => 'cover',
				'cat' => self::CAT,
				'type_range' => 'cover|fit',
				'description' => 'Image header display',
				'details' => 'The image header can be displayed as a cover or fit. Cover will crop the image to fit the header, while fit will scale the image to fit the header.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 1,
				'order' => 16,
			],
			[
				'key' => 'flow_image_header_height',
				'value' => '24',
				'cat' => self::CAT,
				'type_range' => self::POSITIVE,
				'description' => 'Image header height',
				'details' => 'Heights of the image header in rem.',
				'is_secret' => false,
				'is_expert' => true,
				'level' => 1,
				'order' => 17,
			],
			[
				'key' => 'flow_carousel_enabled',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Enable image carousel in the flow',
				'details' => 'Display a preview of the images in a carousel under the image header. This only applied if the image header is enabled.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 1,
				'order' => 18,
			],
			[
				'key' => 'flow_carousel_height',
				'value' => '6',
				'cat' => self::CAT,
				'type_range' => self::POSITIVE,
				'description' => 'Carousel height',
				'details' => 'Heights of the image carousel in rem.',
				'is_secret' => false,
				'is_expert' => true,
				'level' => 1,
				'order' => 19,
			],
			[
				'key' => 'date_format_flow_published',
				'value' => 'M j, Y, g:i:s A e',
				'cat' => self::CAT,
				'type_range' => self::STRING_REQ,
				'description' => 'Format the date displayed in the flow',
				'details' => 'See <a class="underline" href="https://www.php.net/manual/en/datetime.format.php">datetime.format.php</a>',
				'is_secret' => false,
				'is_expert' => true,
				'level' => 1,
				'order' => 20,
			],
			[
				'key' => 'date_format_flow_min_max',
				'value' => 'F Y',
				'cat' => self::CAT,
				'type_range' => self::STRING_REQ,
				'description' => 'Format the min-max date.',
				'details' => 'See <a class="underline" href="https://www.php.net/manual/en/datetime.format.php">datetime.format.php</a>',
				'is_secret' => false,
				'is_expert' => true,
				'level' => 1,
				'order' => 21,
			],
			[
				'key' => 'flow_blur_nsfw_enabled',
				'value' => '1',
				'cat' => self::NSFW,
				'type_range' => self::BOOL,
				'description' => 'Blur sensitive albums in Flow',
				'details' => 'Photos form albums marked as sensitive will be blurred in the flow. Users can unblur them by clicking on the album.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 32766,
			],
			[
				'key' => 'hide_nsfw_in_flow',
				'value' => '1',
				'cat' => self::NSFW,
				'type_range' => self::BOOL,
				'description' => 'Do not show sensitive albums in Flow',
				'details' => 'Albums marked as sensitive will not be shown in the flow.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 32767,
			],
		];
	}
};
