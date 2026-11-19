<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigrationReversed;

/**
 * Lychee no longer updates itself from the web interface (git pull / composer).
 */
return new class() extends BaseConfigMigrationReversed {
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'allow_online_git_pull',
				'value' => '1',
				'is_secret' => true,
				'cat' => 'Admin',
				'type_range' => self::BOOL,
				'description' => 'Allow git pull via web interface',
				'order' => 2,
				'details' => '',
				'not_on_docker' => true,
				'level' => 0,
				'is_expert' => true,
			],
			[
				'key' => 'apply_composer_update',
				'value' => '0',
				'is_secret' => true,
				'cat' => 'Admin',
				'type_range' => self::BOOL,
				'description' => 'Apply composer update on lychee update via web interface',
				'order' => 3,
				'details' => '',
				'not_on_docker' => true,
				'level' => 0,
				'is_expert' => true,
			],
		];
	}
};
