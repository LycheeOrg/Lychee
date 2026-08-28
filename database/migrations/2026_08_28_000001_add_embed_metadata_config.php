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
				'key' => 'embed_metadata_in_files_enabled',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Embed title, description, tags and owner rating into the original/RAW file.',
				'details' => '<span class="pi pi-exclamation-triangle text-orange-500 mr-1"></span> Enabling this rewrites the Original file (and RAW file, if present) whenever a photo\'s title, description, tags, or the owner\'s rating changes. This <b>changes the file\'s contents and checksum</b> — if you later re-import or rescan an untouched copy of the same source file, it will no longer be recognized as a duplicate of the photo already in your library. <b>Local storage only</b> — photos stored on S3 (or any other non-local disk) are skipped and never have metadata embedded.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 21,
			],
			[
				'key' => 'embed_metadata_update_checksum_enabled',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Update stored checksum after embedding metadata.',
				'details' => 'When on (default), the photo\'s recorded checksum is updated to match the rewritten file, keeping the duplicate finder consistent. When off, the recorded checksum keeps pointing at the original pristine upload even after the file has changed — useful if you need the original checksum to stay a permanent fingerprint of what was first uploaded. Only has an effect when "Embed metadata" above is also enabled.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 22,
			],
		];
	}
};
