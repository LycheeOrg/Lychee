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
				'key' => 'pdf_mediabox_max_matches',
				'value' => '25',
				'cat' => self::CAT,
				'type_range' => self::POSITIVE,
				'description' => 'Maximum number of /MediaBox occurrences scanned in a PDF.',
				'details' => 'A PDF declaring more /MediaBox occurrences than this within the first MB of the file is rejected before thumbnail generation, to bound Ghostscript rasterization cost against malicious uploads. Legitimate large scanned multi-page documents can exceed the default; raise this value if such a document is incorrectly rejected.',
				'is_secret' => false,
				'is_expert' => true,
				'level' => 0,
				'order' => 95,
			],
		];
	}
};
