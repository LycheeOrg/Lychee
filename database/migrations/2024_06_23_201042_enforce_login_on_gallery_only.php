<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const GALLERY = 'Gallery';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'login_required_root_only',
				'value' => '1',
				'is_secret' => false,
				'cat' => self::GALLERY,
				'type_range' => self::BOOL,
				'description' => 'Require user to login only on root. A user with a direct link to an album can still access it.',
			],
		];
	}
};
