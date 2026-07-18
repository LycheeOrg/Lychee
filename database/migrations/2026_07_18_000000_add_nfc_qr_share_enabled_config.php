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
				'key' => 'nfc_share_enabled',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Enable sharing the current photo or album URL via NFC.',
				'details' => 'When enabled and the device supports Web NFC, a button is shown in the photo and album header bars to share the current URL by tapping an NFC tag or another NFC-capable device.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 90,
			],
			[
				'key' => 'photo_share_card_enabled',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Enable the photo share card in the photo header bar.',
				'details' => 'When enabled, a button is shown in the photo header bar which opens a card displaying the photo title, a QR code linking directly to the photo, the website owner and the photo license.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 91,
			],
		];
	}
};
