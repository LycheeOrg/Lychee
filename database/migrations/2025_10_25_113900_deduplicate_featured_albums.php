<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const MOD_SMART_ALBUMS = 'Smart Albums';

	/**
	 * @return array<int,array{key:string,value:string,is_secret:bool,cat:string,type_range:string,description:string,order?:int,not_on_docker?:bool,is_expert?:bool}>
	 */
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'deduplicate_pinned_albums',
				'value' => '0',
				'cat' => self::MOD_SMART_ALBUMS,
				'type_range' => self::BOOL,
				'description' => 'Deduplicate featured albums.',
				'details' => 'Featured albums will only appear once on the main gallery page.',
				'is_secret' => false,
				'is_expert' => false,
				'order' => 22,
				'not_on_docker' => false,
				'level' => 0,
			],
		];
	}
};
