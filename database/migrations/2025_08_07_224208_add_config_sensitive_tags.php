<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const MOD_NSFW = 'Mod NSFW';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'hide_nsfw_in_tag_albums',
				'value' => '1', // safe default
				'cat' => self::MOD_NSFW,
				'type_range' => self::BOOL,
				'description' => 'Do not show sensitive photos in Tag Albums',
				'details' => 'Pictures placed in sensive albums will not be shown in Tag Albums.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 32767,
			],
			[
				'key' => 'hide_nsfw_in_tag_listing',
				'value' => '1', // safe default
				'cat' => self::MOD_NSFW,
				'type_range' => self::BOOL,
				'description' => 'Do not show sensitive photos in tag listings',
				'details' => 'Pictures placed in sensive albums will not be shown on the phto listing of a given tag.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 32767,
			],
		];
	}
};

