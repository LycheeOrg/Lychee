<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CAT = 'Smart Albums';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'SA_override_visibility',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Smart album visibility overrides the photo visibility.',
				'details' => '<span class="pi pi-exclamation-triangle text-orange-500"></span> This will make any photos matching the smart album condition visible.',
				'is_expert' => true,
				'is_secret' => false,
				'level' => 0,
				'order' => 10,
			],
			[
				'key' => 'TA_override_visibility',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Tag album visibility overrides the photo visibility.',
				'details' => '<span class="pi pi-exclamation-triangle text-orange-500"></span> This will make any photos matching the tag album condition visible.',
				'is_expert' => true,
				'is_secret' => false,
				'level' => 0,
				'order' => 11,
			],
		];
	}
};