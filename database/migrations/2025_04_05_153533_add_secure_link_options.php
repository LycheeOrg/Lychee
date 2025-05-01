<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CAT = 'Mod Privacy';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'temporary_image_link_enabled',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Enable temporary image links',
				'details' => 'All images will be served with a signed URL. This is a security feature to prevent hotlinking and unauthorized access to images.',
				'is_expert' => false,
				'is_secret' => false,
				'level' => 0,
				'order' => 1,
			],
			[
				'key' => 'temporary_image_link_when_logged_in',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Enable temporary image links for logged in users',
				'details' => '',
				'is_expert' => false,
				'is_secret' => false,
				'level' => 0,
				'order' => 2,
			],
			[
				'key' => 'temporary_image_link_when_admin',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Enable temporary image links for admins',
				'details' => '',
				'is_expert' => false,
				'is_secret' => false,
				'level' => 0,
				'order' => 3,
			],
			[
				'key' => 'temporary_image_link_life_in_seconds',
				'value' => '86400',
				'cat' => self::CAT,
				'type_range' => self::POSITIVE,
				'description' => 'Maximum life time for temporary links in seconds (default is 86400s = 24 hours)',
				'details' => '<i class="pi pi-exclamation-triangle text-orange-500"></i> If you are using request caching, set this value to a higher value than the cache expiration time.',
				'is_expert' => true,
				'is_secret' => false,
				'level' => 0,
				'order' => 4,
			],
			[
				'key' => 'secure_image_link_enabled',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Encrypt image links to protect them ',
				'details' => 'This ensures that the image links are not guessable.',
				'is_expert' => true,
				'is_secret' => false,
				'level' => 1,
				'order' => 5,
			],
		];
	}
};
