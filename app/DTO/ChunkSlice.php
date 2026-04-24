<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO;

/**
 * Value object representing a slice of the photo set for chunked ZIP download.
 */
class ChunkSlice
{
	public function __construct(
		public readonly int $offset,
		public readonly int $limit,
		public readonly int $chunk,
	) {
	}
}
