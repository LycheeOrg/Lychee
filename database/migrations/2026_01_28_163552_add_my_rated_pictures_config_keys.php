<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const SMART_ALBUMS = 'Smart Albums';

	/**
	 * @return array<int,array{key:string,value:string,is_secret:bool,cat:string,type_range:string,description:string,order?:int,not_on_docker?:bool,is_expert?:bool,level?:int}>
	 */
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'enable_my_rated_pictures',
				'value' => '1',
				'cat' => self::SMART_ALBUMS,
				'type_range' => self::BOOL,
				'description' => 'Enable My Rated Pictures smart album.',
				'details' => 'Shows all photos rated by the current user.',
				'is_secret' => false,
				'is_expert' => false,
				'order' => 50,
				'not_on_docker' => false,
				'level' => 0,
			],
			[
				'key' => 'enable_my_best_pictures',
				'value' => '1',
				'cat' => self::SMART_ALBUMS,
				'type_range' => self::BOOL,
				'description' => 'Enable My Best Pictures smart album.',
				'details' => 'Show top-rated photos by the current user.',
				'is_secret' => false,
				'is_expert' => false,
				'order' => 51,
				'not_on_docker' => false,
				'level' => 1,
			],
			[
				'key' => 'my_best_pictures_count',
				'value' => '50',
				'cat' => self::SMART_ALBUMS,
				'type_range' => self::POSITIVE,
				'description' => 'Number of photos in My Best Pictures album.',
				'details' => 'Number of top-rated photos to show in My Best Pictures album. Photos tied at the cutoff are included.',
				'is_secret' => false,
				'is_expert' => false,
				'order' => 52,
				'not_on_docker' => false,
				'level' => 1,
			],
		];
	}
};
