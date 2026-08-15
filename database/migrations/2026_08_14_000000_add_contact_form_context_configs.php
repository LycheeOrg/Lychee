<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CAT = 'contact';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'contact_form_enabled_on_gallery',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Show the contact form link on the gallery page',
				'details' => 'When enabled (and the contact form is enabled), a link to the contact form is displayed at the bottom of the gallery page.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 1,
				'order' => 14,
			],
			[
				'key' => 'contact_form_enabled_on_album',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Show the contact form link on album pages',
				'details' => 'When enabled (and the contact form is enabled), a link to the contact form is displayed at the bottom of album pages.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 1,
				'order' => 15,
			],
			[
				'key' => 'contact_form_enabled_for_logged_in',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Show the contact form link to logged-in users',
				'details' => 'When disabled, logged-in users will not see the contact form link. Administrators always see it regardless of this setting.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 1,
				'order' => 16,
			],
		];
	}
};
