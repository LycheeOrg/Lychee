<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const MOD_GALLERY = 'Gallery';

	/**
	 * @return array<int,array{key:string,value:string,is_secret:bool,cat:string,type_range:string,description:string,order?:int,not_on_docker?:bool,is_expert?:bool}>
	 */
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'desktop_dock_full_transparency_enabled',
				'value' => '0',
				'cat' => self::MOD_GALLERY,
				'type_range' => self::BOOL,
				'description' => 'Enable full dock transparency for desktop.',
				'details' => 'On the photo view, actions at the top of the page are slightly transparent. Enable this to have them fully transparent and only appear on hover.',
				'is_secret' => false,
				'is_expert' => true,
				'order' => 40,
				'not_on_docker' => false,
				'level' => 0,
			],
			[
				'key' => 'mobile_dock_full_transparency_enabled',
				'value' => '0',
				'cat' => self::MOD_GALLERY,
				'type_range' => self::BOOL,
				'description' => 'Enable full dock transparency for mobile.',
				'details' => '<span class="pi pi-exclamation-triangle text-orange-500"></span> This will impact usability on mobile. On the photo view, actions at the top of the page are slightly transparent. Enable this to have them fully transparent and only appear on tap.',
				'is_secret' => false,
				'is_expert' => true,
				'order' => 41,
				'not_on_docker' => false,
				'level' => 0,
			],
		];
	}
};
