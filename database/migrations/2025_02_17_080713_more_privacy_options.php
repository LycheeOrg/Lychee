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
				'key' => 'exif_disabled_for_all',
				'value' => '0',
				'cat' => 'Mod Privacy',
				'type_range' => self::BOOL,
				'description' => 'Disable details and overlay panels in front-end.',
				'details' => '<span class="pi pi-exclamation-triangle text-orange-500"></span> This will not remove the data from the API end-point.',
				'is_secret' => false,
				'level' => 1,
			],
			[
				'key' => 'file_name_hidden',
				'value' => '0',
				'cat' => 'Mod Privacy',
				'type_range' => self::BOOL,
				'description' => 'Do not show the photo title to anonymous users.',
				'details' => 'Logged in user will still have access to the title.',
				'is_secret' => false,
				'level' => 1,
			],
		];
	}
};
