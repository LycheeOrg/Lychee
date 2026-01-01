<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const PROCESSING = 'Image Processing';

	/**
	 * @return array<int,array{key:string,value:string,is_secret:bool,cat:string,type_range:string,description:string,order?:int,not_on_docker?:bool,is_expert?:bool}>
	 */
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'extract_zip_on_upload',
				'value' => '1',
				'cat' => self::PROCESSING,
				'type_range' => self::BOOL,
				'description' => 'Extract uploaded zip file and import content.',
				'details' => 'Zip file will stay on your server unless it is properly extracted without faults (after which it is removed).',
				'is_secret' => false,
				'order' => 27,
				'not_on_docker' => false,
				'level' => 1, // Only for SE.
			],
		];
	}
};
