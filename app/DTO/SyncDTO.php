<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO;

class SyncDTO
{
	/**
	 * Sorting criterion.
	 *
	 * @param string[] $files
	 * @param string[] $directories
	 *
	 * @return void
	 */
	public function __construct(
		public array $files,
		public array $directories)
	{
	}

	/**
	 * Return true if we are only treating files.
	 *
	 * @return bool
	 */
	public function isFilesOnly(): bool
	{
		return count($this->directories) === 0 && count($this->files) > 0;
	}
}
