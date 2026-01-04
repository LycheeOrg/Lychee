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
	 * @return array<int,array{key:string,value:string,is_secret:bool,cat:string,type_range:string,description:string,details?:string,order?:int,not_on_docker?:bool,is_expert?:bool,level?:int}>
	 */
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'album_layout',
				'value' => 'grid',
				'cat' => self::MOD_GALLERY,
				'type_range' => 'grid|list',
				'description' => 'Default album view layout.',
				'details' => 'Choose between grid (thumbnail cards) or list (detailed rows) view for albums. Users can toggle between views client-side, but preference does not persist across page reloads.',
				'is_secret' => false,
				'is_expert' => false,
				'order' => 50,
				'not_on_docker' => false,
				'level' => 0,
			],
		];
	}
};
