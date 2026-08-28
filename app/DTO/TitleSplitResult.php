<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO;

/**
 * Result of {@link \App\Services\TitleSplitter::split()}: the case-folded
 * sortable prefix of a title and its optional trailing numeric suffix.
 */
class TitleSplitResult
{
	public function __construct(
		public readonly string $base,
		public readonly ?int $index,
	) {
	}
}
