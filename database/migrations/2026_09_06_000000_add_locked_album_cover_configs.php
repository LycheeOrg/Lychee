<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CAT = 'Gallery';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'show_cover_of_locked_albums',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'is_secret' => false,
				'description' => 'Always show the cover of password-protected albums',
				'details' => 'When enabled, the cover photo of a locked (password-protected) album is displayed to visitors who have not entered the password yet, regardless of which photo was used to determine it.',
				'level' => 0,
				'order' => 104,
				'is_expert' => false,
			],
			[
				'key' => 'show_selected_cover_on_locked_albums',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'is_secret' => false,
				'description' => 'Show a manually selected cover on password-protected albums',
				'details' => 'When enabled, the cover photo of a locked album is displayed to visitors who have not entered the password yet, but only when that cover was manually chosen via "Set as Cover" rather than automatically selected. This takes effect even when "Always show the cover of password-protected albums" is disabled.',
				'level' => 0,
				'order' => 105,
				'is_expert' => false,
			],
		];
	}
};
