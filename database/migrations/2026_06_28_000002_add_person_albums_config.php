<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CAT = 'Smart Albums';
	public const CAT_NSFW = 'Mod NSFW';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'PA_override_visibility',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Person album visibility overrides the photo visibility.',
				'details' => '<span class="pi pi-exclamation-triangle text-orange-500"></span> This will make any photos matching the persons condition visible.',
				'is_secret' => false,
				'is_expert' => true,
				'level' => 0,
				'order' => 1,
			],
			[
				'key' => 'hide_nsfw_in_person_albums',
				'value' => '1',
				'cat' => self::CAT_NSFW,
				'type_range' => self::BOOL,
				'description' => 'Do not show sensitive photos in Person Albums',
				'details' => '<span class="pi pi-exclamation-triangle text-orange-500"></span> Pictures placed in sensitive albums will not be shown in Person Albums.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 2,
			],
			[
				'key' => 'PA_override_searchability',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Person album ignores the searchable flag of a person.',
				'details' => '<span class="pi pi-exclamation-triangle text-orange-500"></span> When enabled, photos of persons who are not searchable are still shown in Person Albums. When disabled, non-searchable persons no longer contribute photos to Person Albums.',
				'is_secret' => false,
				'is_expert' => true,
				'level' => 0,
				'order' => 3,
			],
		];
	}
};
