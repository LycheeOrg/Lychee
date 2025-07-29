<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'details_links_enabled',
				'value' => '0',
				'cat' => 'Gallery',
				'type_range' => self::BOOL,
				'description' => 'Enable image links in photo details',
				'details' => 'Add a small module to allow easy copying of the photo urls in the details panel.',
				'is_secret' => false,
				'level' => 0,
				'order' => 20,
				'not_on_docker' => false,
				'is_expert' => false,
			],
			[
				'key' => 'details_links_public',
				'value' => '0',
				'cat' => 'Gallery',
				'type_range' => self::BOOL,
				'description' => 'Allow anonymous users to acces image links in photo details',
				'details' => '',
				'is_secret' => false,
				'level' => 0,
				'order' => 21,
				'not_on_docker' => false,
				'is_expert' => false,
			],
		];
	}
};