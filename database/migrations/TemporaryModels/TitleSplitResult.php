<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * Standalone copy of {@see \App\DTO\TitleSplitResult}, frozen for use by
 * {@see TitleSplitter} within migrations.
 * Migrations must not depend on live app code, because app code may change
 * or be removed in the future while old migrations must remain runnable.
 */
class TitleSplitResult
{
	public function __construct(
		public readonly string $base,
		public readonly int $index,
	) {
	}
}
