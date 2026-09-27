<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CAT = 'access_permissions';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'gallery_password',
				'value' => '',
				'cat' => self::CAT,
				'type_range' => 'password',
				'description' => 'Password required to open the gallery',
				'details' => 'Visitors must enter this password before seeing anything. Signed-in users are never asked. Photo files are only protected when secure image links are enabled. For privacy, a gallery password disables RSS feeds and album embeds.',
				'is_secret' => true,
				'is_expert' => false,
				'level' => 0,
				'order' => 13,
			],
			[
				'key' => 'gallery_password_cookie_lifetime',
				'value' => '30',
				'cat' => self::CAT,
				'type_range' => 'int:0:3650',
				'description' => 'Days a visitor stays unlocked after entering the gallery password',
				'details' => '0 keeps the gallery unlocked until the browser is closed.',
				'is_secret' => false,
				'is_expert' => true,
				'level' => 0,
				'order' => 14,
			],
		];
	}
};
