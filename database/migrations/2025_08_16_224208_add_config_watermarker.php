<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	public const MOD_WATERMARKER = 'Mod Watermarker';
	public const BOOL = '0|1';
	public const POSITIVE = 'positive';
	public const INT = 'int';
	public const STRING = 'string';

	/**
	 * Run the migrations.
	 */
	final public function up(): void
	{
		DB::table('config_categories')->insert([
			['cat' => self::MOD_WATERMARKER, 'name' => 'Watermarker', 'description' => 'This modules enable watermarking on photos.
			The watermark is configurable by giving the <pre class="inline px-1">photoId</pre> of the image you want to apply.
			This id is the last 24 character identifier in the URL when you open an image. We recommend you use a png image with transparent background for better results.<br><br>
			<span class="pi pi-exclamation-triangle text-orange-500 mr-1"></span> Enabling this module will <b>double</b> the file-storage usage on your server.', 'order' => 22],
		]);

		DB::table('configs')->insert($this->getConfigs());
	}

	/**
	 * Reverse the migrations.
	 *
	 * @codeCoverageIgnore Tested but after CI run...
	 */
	final public function down(): void
	{
		$keys = collect($this->getConfigs())->map(fn ($v) => $v['key'])->all();
		DB::table('configs')->whereIn('key', $keys)->delete();
		DB::table('config_categories')->where('cat', self::MOD_WATERMARKER)->delete();
	}

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'watermark_enabled',
				'value' => '0',
				'cat' => self::MOD_WATERMARKER,
				'type_range' => self::BOOL,
				'description' => 'Enable watermarking of photos',
				'details' => 'Uploaded photos will be watermarked with the configured watermark image.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 1,
				'order' => 1,
			],
			[
				'key' => 'watermark_photo_id',
				'value' => '',
				'cat' => self::MOD_WATERMARKER,
				'type_range' => self::STRING,
				'description' => 'Watermark photo id',
				'details' => 'Photo Id (24 character sequence) of the image used for watermarking photos. We recommend png with transparency.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 1,
				'order' => 2,
			],
			[
				'key' => 'watermark_random_path',
				'value' => '1', // Safe default
				'cat' => self::MOD_WATERMARKER,
				'type_range' => self::BOOL,
				'description' => 'Use random path for watermarked images',
				'details' => 'If disabled, the watermark image path will be the same as the current path but with a suffix.',
				'is_secret' => true,
				'is_expert' => true,
				'level' => 1,
				'order' => 3,
			],
			[
				'key' => 'watermark_public',
				'value' => '1',
				'cat' => self::MOD_WATERMARKER,
				'type_range' => self::BOOL,
				'description' => 'Show watermark on public photos',
				'details' => 'Anonymous users will see watermarked photos.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 1,
				'order' => 4,
			],
			[
				'key' => 'watermark_logged_in_users_enabled',
				'value' => '0',
				'cat' => self::MOD_WATERMARKER,
				'type_range' => self::BOOL,
				'description' => 'Show watermark to logged in users',
				'details' => 'Logged-in users will see watermarked photos.',
				'is_secret' => false,
				'is_expert' => true,
				'level' => 1,
				'order' => 5,
			],
			[
				'key' => 'watermark_original',
				'value' => '0',
				'cat' => self::MOD_WATERMARKER,
				'type_range' => self::BOOL,
				'description' => 'Also watermark the original photo',
				'details' => '',
				'is_secret' => true,
				'is_expert' => true,
				'level' => 1,
				'order' => 6,
			],
			[
				'key' => 'watermark_size',
				'value' => '50',
				'cat' => self::MOD_WATERMARKER,
				'type_range' => self::POSITIVE,
				'description' => 'Watermark size on the image, from 1 to 100%',
				'details' => 'This represent the quantity of the image covered by the watermark.',
				'is_secret' => true,
				'is_expert' => false,
				'level' => 1,
				'order' => 7,
			],
			[
				'key' => 'watermark_opacity',
				'value' => '75',
				'cat' => self::MOD_WATERMARKER,
				'type_range' => self::POSITIVE,
				'description' => 'Watermark opacity ranging from 1 to 100%',
				'details' => '1 - nearly invisible, 100 - completely opaque. We recommend to not go under 25.',
				'is_secret' => true,
				'is_expert' => false,
				'level' => 1,
				'order' => 8,
			],
			[
				'key' => 'watermark_position',
				'value' => 'center',
				'cat' => self::MOD_WATERMARKER,
				'type_range' => 'top-left|top|top-right|left|center|right|bottom-left|bottom|bottom-right',
				'description' => 'Watermark position on the image',
				'details' => '',
				'is_secret' => true,
				'is_expert' => false,
				'level' => 1,
				'order' => 9,
			],
			[
				'key' => 'watermark_shift_type',
				'value' => 'relative',
				'cat' => self::MOD_WATERMARKER,
				'type_range' => 'relative|absolute',
				'description' => 'Shift the watermark relatively to the size',
				'details' => 'When using relative, the watermark will be shifted proportionally to the size of the image.<br>When using absolute the watermark will be shifted by a quantity of pixels.',
				'is_secret' => true,
				'is_expert' => true,
				'level' => 1,
				'order' => 10,
			],
			[
				'key' => 'watermark_shift_x',
				'value' => '0',
				'cat' => self::MOD_WATERMARKER,
				'type_range' => self::INT,
				'description' => 'Horizontal shift',
				'details' => 'Number of pixel/proportional translation applied horizontally to the watermark.',
				'is_secret' => true,
				'is_expert' => true,
				'level' => 1,
				'order' => 11,
			],
			[
				'key' => 'watermark_shift_x_direction',
				'value' => 'right',
				'cat' => self::MOD_WATERMARKER,
				'type_range' => 'left|right',
				'description' => 'Direction of the horizontal shift',
				'details' => 'Direction of the translation applied to the watermark: to the left or to the right?',
				'is_secret' => true,
				'is_expert' => true,
				'level' => 1,
				'order' => 12,
			],
			[
				'key' => 'watermark_shift_y',
				'value' => '0',
				'cat' => self::MOD_WATERMARKER,
				'type_range' => self::INT,
				'description' => 'Vertical shift',
				'details' => 'Number of pixel/proportional translation applied vertically to the watermark.',
				'is_secret' => true,
				'is_expert' => true,
				'level' => 1,
				'order' => 13,
			],
			[
				'key' => 'watermark_shift_y_direction',
				'value' => 'up',
				'cat' => self::MOD_WATERMARKER,
				'type_range' => 'up|down',
				'description' => 'Direction of the vertical shift',
				'details' => 'Direction of the translation applied to the watermark: up or down?',
				'is_secret' => true,
				'is_expert' => true,
				'level' => 1,
				'order' => 14,
			],
		];
	}
};

