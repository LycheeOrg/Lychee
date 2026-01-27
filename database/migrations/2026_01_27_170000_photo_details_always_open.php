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
				'key' => 'enable_photo_details_always_open',
				'value' => '0',
				'cat' => self::MOD_GALLERY,
				'type_range' => self::BOOL,
				'description' => 'Photo details always visible.',
				'details' => 'When opening the photo view, the photo details drawer is always visible.',
				'is_secret' => false,
				'is_expert' => true,
				'order' => 80,
				'not_on_docker' => false,
				'level' => 0,
			],
		];
	}
};
