<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const GALLERY = 'Gallery';
	public const VALUES = 'anonymous|authenticated|editor';

	/**
	 * @return array<int,array{key:string,value:string,is_secret:bool,cat:string,type_range:string,description:string,order?:int,not_on_docker?:bool,is_expert?:bool,level?:int}>
	 */
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'photos_star_visibility',
				'value' => 'editor',
				'cat' => self::GALLERY,
				'type_range' => self::VALUES,
				'description' => 'Who can see and star photos.',
				'details' => 'Option to configure who can see star flag on a photo and star/unstar.',
				'is_secret' => false,
				'is_expert' => false,
				'order' => 10,
				'not_on_docker' => false,
				'level' => 2,
			],
		];
	}
};
