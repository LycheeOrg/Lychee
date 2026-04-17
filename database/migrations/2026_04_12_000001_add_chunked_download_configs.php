<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CAT = 'Image Processing';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'download_archive_chunked',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Enable chunked ZIP archive downloads.',
				'details' => 'When enabled, large album downloads are split into multiple smaller ZIP files, each containing a configurable number of photos.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 18,
			],
			[
				'key' => 'download_archive_chunk_size',
				'value' => '300',
				'cat' => self::CAT,
				'type_range' => self::POSITIVE,
				'description' => 'Number of photos per ZIP chunk.',
				'details' => 'When chunked downloads are enabled, each ZIP archive will contain at most this many photos.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 19,
			],
			[
				'key' => 'download_archive_drop_extension_enabled',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Drop extensions in file names/titles in ZIP archives.',
				'details' => 'When enabled the extension of files in zip archives will be dropped. This is useful if your photos have titles such as image.jpg to avoid having files named image.jpg.jpg in the zip archive.',
				'is_secret' => false,
				'is_expert' => true,
				'level' => 0,
				'order' => 20,
			],
		];
	}
};
