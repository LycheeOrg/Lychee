<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const OAUTH = 'OAuth & SSO';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'oauth_create_user_on_first_attempt',
				'value' => '0',
				'is_secret' => true,
				'cat' => self::OAUTH,
				'type_range' => '0|1',
				'description' => 'Allow user creation when oauth id does not exist.',
			],
			[
				'key' => 'oauth_grant_new_user_upload_rights',
				'value' => '0',
				'is_secret' => true,
				'cat' => self::OAUTH,
				'type_range' => '0|1',
				'description' => 'Newly created user are allowed to upload content.',
			],
			[
				'key' => 'oauth_grant_new_user_modification_rights',
				'value' => '0',
				'is_secret' => true,
				'cat' => self::OAUTH,
				'type_range' => '0|1',
				'description' => 'Newly created user are allowed to edit their profile.',
			],
		];
	}
};