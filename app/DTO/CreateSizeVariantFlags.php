<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\DTO;

use App\Enum\StorageDiskType;

final readonly class CreateSizeVariantFlags
{
	public function __construct(
		public bool $is_backup = false,
		public bool $is_watermark = false,
		public StorageDiskType $disk = StorageDiskType::LOCAL,
	) {
	}

	public function get_suffix(): string
	{
		return match (true) {
			$this->is_backup => '_orig',
			$this->is_watermark => '_wmk',
			default => '',
		};
	}
}
