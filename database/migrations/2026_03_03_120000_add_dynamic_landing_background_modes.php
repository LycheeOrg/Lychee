<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const MOD_WELCOME = 'Mod Welcome';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'landing_background_landscape_mode',
				'value' => 'static', // Default to static to avoid unexpected behavior for existing users
				'cat' => self::MOD_WELCOME,
				'type_range' => 'static|photo_id|random|latest_album_cover|random_from_album',
				'description' => 'Mode for landscape background',
				'details' => 'Options: static (URL), photo_id (specific photo), random (random public photo), latest_album_cover (latest album cover), random_from_album (random from album)',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 3,
			],
			[
				'key' => 'landing_background_portrait_mode',
				'value' => 'static', // Default to static to avoid unexpected behavior for existing users
				'cat' => self::MOD_WELCOME,
				'type_range' => 'static|photo_id|random|latest_album_cover|random_from_album',
				'description' => 'Mode for portrait background',
				'details' => 'Options: static (URL), photo_id (specific photo), random (random public photo), latest_album_cover (latest album cover), random_from_album (random from album)',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 4,
			],
		];
	}
};
