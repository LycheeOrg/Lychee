<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const BOOL = '0|1';
	public const HEADER_SIZE = 'half_screen|full_screen';
	public const CAT_MOD_PRO = 'Mod Pro';

	/**
	 * @return array<int,array{key:string,value:string,is_secret:bool,cat:string,type_range:string,description:string,order?:int,not_on_docker?:bool,is_expert?:bool,level?:int}>
	 */
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'album_enhanced_display_enabled',
				'value' => '0',
				'cat' => self::CAT_MOD_PRO,
				'type_range' => self::BOOL,
				'description' => 'Enable enhanced album header features',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 2,
				'details' => 'Option to enable new style of header with bigger title, button "Open gallery" and allowing customization of title style.',
				'order' => 10,
			],
			[
				'key' => 'album_header_size',
				'value' => 'half_screen',
				'cat' => self::CAT_MOD_PRO,
				'type_range' => self::HEADER_SIZE,
				'description' => 'Global album header image size',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 2,
				'details' => 'Option to configure size of header image.',
				'order' => 11,
			],
			[
				'key' => 'album_header_landing_title_enabled',
				'value' => '0',
				'cat' => self::CAT_MOD_PRO,
				'type_range' => self::BOOL,
				'description' => 'Display the landing title on at the bottom of the Album header.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 2,
				'details' => 'You can configure the landing title in the Landing page module.',
				'order' => 12,
			],
		];
	}
};
