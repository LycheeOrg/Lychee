<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const SMART_ALBUMS = 'Smart Albums';
	public const GALLERY = 'Gallery';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'enable_untagged',
				'value' => '1',
				'cat' => self::SMART_ALBUMS,
				'type_range' => self::BOOL,
				'description' => 'Enable Untagged smart album.',
				'details' => 'If a smart album containing all the untagged photos will be available in the gallery.',
				'is_secret' => false,
				'not_on_docker' => false,
				'level' => 0,
				'order' => 5,
			],
			[
				'key' => 'photos_pagination_limit',
				'value' => '500',
				'cat' => self::SMART_ALBUMS,
				'type_range' => self::POSITIVE,
				'description' => 'Maximum number of photos to display per page in albums.',
				'details' => '',
				'is_secret' => false,
				'not_on_docker' => false,
				'level' => 0,
				'order' => 30,
			],
		];
	}
};
