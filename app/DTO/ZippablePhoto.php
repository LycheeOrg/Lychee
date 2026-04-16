<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO;

use App\Image\Files\BaseMediaFile;
use App\Image\Files\FlysystemFile;

class ZippablePhoto
{
	public function __construct(
		public readonly string $file_name,
		public readonly FlysystemFile|BaseMediaFile $file,
		public readonly ?string $title,
		public readonly ?\DateTimeInterface $last_modification_date_time,
	) {
	}
}