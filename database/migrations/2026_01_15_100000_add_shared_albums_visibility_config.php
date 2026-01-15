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
				'key' => 'shared_albums_visibility_default',
				'value' => 'show',
				'cat' => self::MOD_GALLERY,
				'type_range' => 'show|separate|separate_shared_only|hide',
				'description' => 'Default visibility mode for shared albums in the gallery.',
				'details' => 'Controls how albums shared by other users appear: show (inline with owned albums), separate (in tabs), separate_shared_only (in tabs, direct shares only), hide (not shown).',
				'is_secret' => false,
				'is_expert' => true,
				'order' => 60,
				'not_on_docker' => false,
				'level' => 0,
			],
		];
	}
};
