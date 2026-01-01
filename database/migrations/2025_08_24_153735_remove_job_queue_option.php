<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigrationReversed;

return new class() extends BaseConfigMigrationReversed {
	/**
	 * @return array<int,array{key:string,value:string,is_secret:bool,cat:string,type_range:string,description:string,order?:int,not_on_docker?:bool,is_expert?:bool}>
	 */
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'use_job_queues',
				'value' => '0',
				'is_secret' => false,
				'cat' => 'Image Processing',
				'type_range' => self::BOOL,
				'description' => 'Use job queues instead of directly live connection.',
				'order' => 17,
				'details' => '',
				'not_on_docker' => false,
				'level' => 0,
				'is_expert' => true,
			],
		];
	}
};

