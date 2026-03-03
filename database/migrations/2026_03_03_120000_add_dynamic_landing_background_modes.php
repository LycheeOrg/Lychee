<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Enum\LandingBackgroundModeType;
use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const MOD_WELCOME = 'Mod Welcome';

	public function getConfigs(): array
	{
		$mode_enum = implode('|', array_column(LandingBackgroundModeType::cases(), 'value'));

		return [
			[
				'key' => 'landing_background_landscape_mode',
				'value' => LandingBackgroundModeType::STATIC->value,
				'cat' => self::MOD_WELCOME,
				'type_range' => $mode_enum,
				'description' => 'Mode for landscape background',
				'details' => 'Options: static (URL), photo_id (specific photo), random (random public photo), latest_album_cover (latest album cover), random_from_album (random from album)',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 3,
			],
			[
				'key' => 'landing_background_portrait_mode',
				'value' => LandingBackgroundModeType::STATIC->value,
				'cat' => self::MOD_WELCOME,
				'type_range' => $mode_enum,
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
