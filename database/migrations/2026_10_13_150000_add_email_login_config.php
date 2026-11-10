<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CAT = 'Users Management';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'email_login_enabled',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Allow signing in with the email address.',
				'details' => 'When enabled, the login field accepts either the username or the email address of the account. Users without an email keep signing in with their username.',
				'is_expert' => false,
				'is_secret' => false,
				'level' => 0,
				'order' => 2,
			],
		];
	}
};
