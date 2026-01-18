<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const MOD_GALLERY = 'Gallery';

	/**
	 * @return array<int,array{key:string,value:string,is_secret:bool,cat:string,type_range:string,description:string,order?:int,not_on_docker?:bool,is_expert?:bool,level?:int}>
	 */
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'display_exif_data',
				'value' => '1',
				'cat' => self::MOD_GALLERY,
				'type_range' => self::BOOL,
				'description' => 'Display Exif data.',
				'details' => 'If disabled, Exif data will not be displayed in the UI.',
				'is_secret' => true,
				'is_expert' => true,
				'order' => 70,
				'not_on_docker' => false,
				'level' => 0,
			],
		];
	}
};
