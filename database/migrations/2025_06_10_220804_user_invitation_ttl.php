<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CAT = 'Users Management';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'user_invitation_ttl',
				'value' => '7',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Maximum life time for invitation links in days.',
				'details' => '<span class="pi pi-exclamation-triangle text-orange-500"></span> Invitation links cannot be revoked.',
				'is_expert' => true,
				'is_secret' => true,
				'level' => 0,
				'order' => 3,
			],
		];
	}
};
